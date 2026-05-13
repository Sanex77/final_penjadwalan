<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Lab;

use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function dosenIndex() 
    {
        $myBookings = Booking::where('user_id', auth()->id())->latest()->get();
        $schedules = Schedule::all();
        $labs = Lab::all(); 
        return view('dosen.index', compact('schedules','labs','myBookings'));
    }

    public function mahasiswaIndex() 
    {
        // Tadi ini error karena $schedules belum didefinisikan tapi di-compact
        $schedules = Schedule::all(); 
        $myBookings = Booking::where('user_id', auth()->id())->latest()->get();
        return view('mahasiswa.index', compact('schedules','myBookings'));
    }

    public function store(Request $request)
{
    $user = auth()->user();

    // 1. VALIDASI
    $rules = [
        'tanggal'     => 'required|date',
        'jam_mulai'   => 'required',
        'jam_selesai' => 'required|after:jam_mulai',
        'keperluan'   => 'required|string',
    ];

    if ($user->role === 'dosen' || $user->role === 'spv') {
        $rules['lab'] = 'required|string'; 
        $rules['file_surat'] = 'nullable|mimes:pdf|max:2048';
    } else {
        $rules['lab'] = 'nullable|string'; 
        $rules['file_surat'] = 'required|mimes:pdf|max:2048';
    }

    $request->validate($rules);
    $selectedLab = $request->lab ?? 'TBD';

    // 2. CEK BENTROK (Jadwal Tetap & Booking Approved)
    // ... (Logika cek bentrok kamu sudah benar, lewati saja ke bagian simpan) ...

    // 3. UPLOAD FILE
    $path = null;
    if ($request->hasFile('file_surat')) {
        $path = $request->file('file_surat')->store('surat-booking', 'public');
    }

    // 4. SIMPAN DAN TEMBAK NOTIFIKASI
    try {
        // TAMPUNG HASIL CREATE KE VARIABEL $booking
        $booking = \App\Models\Booking::create([
            'user_id'      => $user->id,
            'tanggal'      => $request->tanggal,
            'hari'         => \Carbon\Carbon::parse($request->tanggal)->locale('id')->translatedFormat('l'),
            'lab'          => $selectedLab,
            'jam_mulai'    => $request->jam_mulai,
            'jam_selesai'  => $request->jam_selesai,
            'keperluan'    => $request->keperluan,
            'kapasitas'    => $request->kapasitas,
            'file_surat'   => $path,
            'status'       => 'pending',
        ]);

        // --- TARUH NOTIFIKASI DI SINI (SEBELUM RETURN) ---
        $spvs = \App\Models\User::where('role', 'spv')->get();
        foreach ($spvs as $spv) {
            $spv->notify(new \App\Notifications\ReservasiMasuk($booking));
        }
    // 4. SIMPAN DAN TEMBAK NOTIFIKASI
    

        // Notifikasi Bawaan Laravel (Biarkan saja kalau mau tetap dipakai)
        $spvs = \App\Models\User::where('role', 'spv')->get();
        foreach ($spvs as $spv) {
            $spv->notify(new \App\Notifications\ReservasiMasuk($booking));
        }

        // 🚀 PANGGIL FUNGSI KIRIM WA KE SPV DI SINI
        $this->sendWaToSpv($booking);

        return back()->with('success', '🚀 Pengajuan Berhasil! Notifikasi WA sudah dikirim ke SPV.');

    } catch (\Exception $e) {
        return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
    
    
}

    public function updateLab(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['lab' => $request->lab]);
        return back()->with('success', 'Lab berhasil ditetapkan ke ' . $request->lab);
    }


    public function getAvailableLabs(Request $request)
{
    $tgl = $request->tanggal;
    $mulai = $request->jam_mulai;
    $selesai = $request->jam_selesai;

    // 1. Cari Lab yang terisi di Jadwal Tetap (Schedules)
    $busySchedules = \App\Models\Schedule::where('tanggal', $tgl)
        ->where(function($query) use ($mulai, $selesai) {
            $query->where('jam_mulai', '<', $selesai)
                  ->where('jam_selesai', '>', $mulai);
        })->pluck('lab')->toArray();

    // 2. Cari Lab yang sudah di-Booking orang lain (Approved)
    $busyBookings = \App\Models\Booking::where('tanggal', $tgl)
        ->where('status', 'approved')
        ->where(function($query) use ($mulai, $selesai) {
            $query->where('jam_mulai', '<', $selesai)
                  ->where('jam_selesai', '>', $mulai);
        })->pluck('lab')->toArray();

    // 3. Gabungkan semua Lab yang sibuk
    $allBusyLabs = array_unique(array_merge($busySchedules, $busyBookings));

    // 4. List semua Lab yang ada (1-11)
    $allLabs = Lab::pluck('nm_lab')->toArray();

    // 5. Lab yang tersedia = Semua Lab dikurangi Lab Sibuk
  $availableLabs = Lab::whereNotIn('nm_lab', $allBusyLabs)
                    ->select('nm_lab', 'kapasitas', 'fasilitas')
                    ->get();

    return response()->json([
        'success' => true,
        'labs' => $availableLabs
    ]);
}
// FUNGSI HELPER UNTUK KIRIM WA KE SEMUA SPV
    private function sendWaToSpv($booking)
    {
        // 1. Cari semua user yang jabatannya SPV
        $spvs = \App\Models\User::where('role', 'spv')->get();

        foreach ($spvs as $spv) {
            // Kalau SPV-nya lupa ngisi nomor WA, lewati saja biar nggak error
            if (empty($spv->no_wa)) continue;

            // 2. Bersihkan nomor WA SPV
            $noWa = preg_replace('/[^0-9]/', '', $spv->no_wa);
            if (str_starts_with($noWa, '0')) {
                $noWa = '62' . substr($noWa, 1);
            } elseif (str_starts_with($noWa, '8')) {
                $noWa = '62' . $noWa;
            }

            // 3. Tentukan dia Dosen atau Mahasiswa/Ormawa
            $roleName = $booking->user->role == 'dosen' ? '👨‍🏫 Dosen' : '🎓 Mahasiswa/Ormawa';

            // 4. Siapkan Template Pesan Khusus Bos (SPV)
            $pesan = "🚨 *PENGAJUAN LAB BARU MASUK* 🚨\n\n" .
                     "Halo *{$spv->name}*,\n" .
                     "Ada reservasi lab baru yang butuh persetujuan Anda:\n\n" .
                     "👤 *Pemohon:* {$booking->user->name} ({$roleName})\n" .
                     "🏢 *Lab:* {$booking->lab}\n" .
                     "📅 *Tanggal:* {$booking->tanggal} ({$booking->hari})\n" .
                     "⏰ *Waktu:* {$booking->jam_mulai} - {$booking->jam_selesai}\n" .
                     "📝 *Keperluan:* {$booking->keperluan}\n\n" .
                     "Silakan login ke Dashboard SPV untuk cek berkas & Approve/Reject.\n\n" .
                     "-- Bot Notifikasi Lab --";

            // 5. Tembak ke Node.js (Gateway Pribadi Kamu)
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://localhost:3001/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 15,          // Udah pakai settingan aman anti-ganda
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode(array(
                    'target' => $noWa,
                    'message' => $pesan
                )),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            curl_exec($curl);
            curl_close($curl);
        }
    }
}