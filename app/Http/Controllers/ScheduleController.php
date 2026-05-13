<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Booking; // Pastikan ini diimpor
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Lab;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    public function welcome(Request $request)
{
    // 1. Tangkap parameter dari URL atau input kalender (contoh: ?filter_date=2026-05-15)
    // Jika tidak ada (web baru di-refresh), otomatis set ke HARI INI (now).
    $filterDate = $request->query('filter_date', now()->toDateString());

    // 2. Tarik data HANYA untuk tanggal yang ada di variabel $filterDate
    $schedules = Schedule::whereDate('tanggal', $filterDate)
                       ->orderBy('jam_mulai', 'asc')
                       ->get();

    // 3. JIKA YANG MINTA DATA ADALAH JAVASCRIPT (AJAX DARI KALENDER)
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json($schedules);
    }

    // 4. JIKA YANG BUKA ADALAH BROWSER NORMAL (PERTAMA KALI REFRESH)
    return view('welcome', compact('schedules', 'filterDate'));
}

    public function tvDisplay()
    {
        $schedules = Schedule::orderBy('tanggal', 'asc')->orderBy('jam_mulai', 'asc')->get();
        return view('tv.display', compact('schedules'));
    }

  
  
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'lab' => 'required',
            'jam_mulai' => 'required',
            'matkul' => 'required',
            'sks' => 'required|numeric',
            'dosen' => 'required',
        ]);

        $menit = $request->sks * 50;
        $jam_selesai = date('H:i', strtotime($request->jam_mulai . " + $menit minutes"));
        $hari = Carbon::parse($request->tanggal)->locale('id')->translatedFormat('l');

        $bentrok = Schedule::where('tanggal', $request->tanggal)
            ->where('lab', $request->lab)
            ->where(function($query) use ($request, $jam_selesai) {
                $query->where('jam_mulai', '<', $jam_selesai)
                      ->where('jam_selesai', '>', $request->jam_mulai);
            })->first();

        if ($bentrok) {
            return back()->with('error', "⚠️ Gagal! Jadwal bentrok dengan matkul: {$bentrok->matkul} ({$bentrok->jam_mulai} - {$bentrok->jam_selesai}) di {$request->lab}.");
        }

        Schedule::create([
            'tanggal' => $request->tanggal,
            'hari' => $hari,
            'lab' => $request->lab,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $jam_selesai,
            'matkul' => $request->matkul,
            'sks' => $request->sks,
            'dosen' => $request->dosen,
        ]);

        return back()->with('success', '✅ Jadwal berhasil ditambahkan!');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return back()->with('success', 'Jadwal dihapus!');
    }

    public function approve($id)
    {
        $booking = Booking::with(['user', 'lab_relation'])->findOrFail($id);

        if ($booking->lab === 'TBD' || empty($booking->lab)) {
            return back()->with('error', '⚠️ Gagal! Lab belum dipilih. Silakan tetapkan Lab terlebih dahulu sebelum menyetujui.');
        }

        // UPDATE STATUS JADI APPROVED (TIDAK DIHAPUS BIAR BISA JADI HISTORI)
        $booking->update([
            'status' => 'approved'
        ]);

        $hari = \Carbon\Carbon::parse($booking->tanggal)->locale('id')->translatedFormat('l');

        Schedule::create([
            'tanggal'     => $booking->tanggal,
            'hari'        => $hari,
            'lab'         => $booking->lab, 
            'jam_mulai'   => $booking->jam_mulai,
            'jam_selesai' => $booking->jam_selesai,
            'matkul'      => $booking->keperluan,
            'sks'         => $booking->sks ?? 0,
            'dosen'       => $booking->user->name ?? 'No Name',
        ]);

        $this->sendWhatsAppNotification($booking);

        return back()->with('success', '✅ Booking disetujui, masuk ke jadwal tetap, dan WA terkirim!');
    }

    private function sendWhatsAppNotification($booking)
    {
        $noWa = $booking->user->no_wa;
        
        if (str_starts_with($noWa, '0')) {
            $noWa = '62' . substr($noWa, 1);
        } elseif (str_starts_with($noWa, '+')) {
            $noWa = substr($noWa, 1);
        }

        $templatePesan = "Halo *{$booking->user->name}*,\n\n" .
                         "Pengajuan peminjaman Lab kamu telah *DISETUJUI*! ✅\n\n" .
                         "*Detail Jadwal:*\n" .
                         "🏢 Lab: {$booking->lab}\n" .
                         "📅 Tanggal: {$booking->tanggal} ({$booking->hari})\n" .
                         "⏰ Jam: {$booking->jam_mulai} - {$booking->jam_selesai}\n" .
                         "📝 Keperluan: {$booking->keperluan}\n\n" .
                         "Silakan gunakan lab sesuai jadwal. Terima kasih!\n" .
                         "-- Admin Lab Komputer --";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://127.0.0.1:3001/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                'target' => $noWa,
                'message' => $templatePesan
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        // ✅ PERBAIKAN BUG CURL (DIBEKUKAN BIAR NGGAK CRASH)
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
       
        if ($err) {
            Log::error("CURL Error WA Gateway: " . $err);
        } else {
            Log::info("Respon dari Node JS: " . $response);
        }
        
        return $response;
    }

    public function reject($id)
    {
        try {
            $booking = Booking::findOrFail($id);
            
            // UPDATE STATUS JADI REJECTED (TIDAK DIHAPUS BIAR JADI HISTORI)
            $booking->update([
                'status' => 'rejected'
            ]);

            return back()->with('error', 'SYSTEM_MSG: Pengajuan telah ditolak!');
        } catch (\Exception $e) {
            return back()->with('error', 'CRITICAL_ERROR: ' . $e->getMessage());
        }
    }

    public function quickUpdate(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        
        $schedule->update([
            'nama_asisten' => $request->nama_asisten
        ]);

        return back()->with('success', 'Asisten diperbarui!');
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $hariPatokan    = $schedule->hari;
        $matkulPatokan  = $schedule->matkul;
        $labPatokan     = $schedule->lab;
        $jamPatokan     = $schedule->getOriginal('jam_mulai');
        $tanggalPatokan = $schedule->tanggal;

        $newAsisten = $request->has('nama_asisten') ? $request->nama_asisten : $schedule->nama_asisten;

        $schedule->update([
            'tanggal'      => $request->tanggal ?? $schedule->tanggal,
            'hari'         => $request->tanggal ? Carbon::parse($request->tanggal)->locale('id')->translatedFormat('l') : $schedule->hari,
            'lab'          => $request->lab ?? $schedule->lab,
            'jam_mulai'    => $request->jam_mulai ?? $schedule->jam_mulai,
            'jam_selesai'  => $request->jam_selesai ?? $schedule->jam_selesai,
            'matkul'       => $request->matkul ?? $schedule->matkul,
            'dosen'        => $request->dosen ?? $schedule->dosen,
            'nama_asisten' => $newAsisten,
        ]);

        $jumlahLooping = 0;
        if ($request->has('nama_asisten')) {
            $jumlahLooping = Schedule::where('hari', $hariPatokan)
                ->where('matkul', $matkulPatokan)
                ->where('lab', $labPatokan)
                ->where('jam_mulai', $jamPatokan)
                ->where('tanggal', '>', $tanggalPatokan)
                ->update([
                    'nama_asisten' => $newAsisten
                ]);
        }

        return back()->with('success', "✅ Mantap! Perubahan berhasil disimpan & Asisten otomatis di-copy ke $jumlahLooping jadwal di minggu berikutnya.");
    }

    public function importExcel(Request $request) 
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        $sheets = Excel::toArray([], $request->file('file_excel'));
        $count = 0;

        foreach ($sheets as $rows) {
            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue; 
                if (isset($row[1]) && str_contains(strtolower($row[1]), 'mata kuliah')) continue;

                $matkulRaw = $row[1] ?? '';
                $sks       = $row[3] ?? 0;
                $kelp      = $row[4] ?? '';
                $hariExcel = trim($row[5] ?? '');
                $jamStr    = $row[7] ?? '';
                $ruangRaw  = $row[8] ?? '';
                $dosen     = $row[9] ?? '';
                $asisten   = $row[10]??'';

                if (str_contains(strtoupper($ruangRaw), 'LAB')) {
                    preg_match('/LAB\s?\d+/i', strtoupper($ruangRaw), $matches);
                    $namaLab = $matches[0] ?? 'LAB TBD';

                    $jamSplit = explode('-', $jamStr);
                    $jamMulai = trim($jamSplit[0]);
                    $jamSelesai = isset($jamSplit[1]) ? trim($jamSplit[1]) : '00:00';

                    $period = CarbonPeriod::create($request->start_date, $request->end_date);
                    
                    foreach ($period as $date) {
                        if (strtolower($date->locale('id')->translatedFormat('l')) == strtolower($hariExcel)) {
                            Schedule::create([
                                'tanggal'     => $date->format('Y-m-d'),
                                'hari'        => $hariExcel,
                                'lab'         => strtoupper($namaLab),
                                'jam_mulai'   => $jamMulai,
                                'jam_selesai' => $jamSelesai,
                                'matkul'      => strtoupper($matkulRaw) . " ($kelp)",
                                'sks'         => $sks,
                                'dosen'       => $dosen,
                                'nama_asisten' => $asisten,
                            ]);
                            $count++;
                        }
                    }
                }
            }
        }

        if ($count === 0) {
            return back()->with('error', 'Waduh, datanya kebaca tapi nggak nemu satupun jadwal yang ruangannya LAB.');
        }

        return back()->with('success', "🚀 Mantap! $count baris jadwal khusus LAB berhasil di-generate otomatis.");
    }

    public function clearSchedule()
    {
        Schedule::truncate();
        return back()->with('success', '🧹 Wusss! Semua jadwal tetap berhasil disapu bersih. ID kembali ke 1.');
    }

    public function importAsistenExcel(Request $request) 
    {
        $request->validate([
            'file_asisten' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray([], $request->file('file_asisten'));
        $count = 0;

        foreach ($sheets as $rows) {
            $currentAssistant = 'TBD';
            
            // 🚀 BUFFER SAKTI: Tempat nahan data sementara buat digabung
            $pendingSchedules = [];

            // Fungsi mini buat masukin data ke DB kalau jamnya udah beres
            $flushSchedule = function($hari) use (&$pendingSchedules, &$count) {
                if (isset($pendingSchedules[$hari])) {
                    \App\Models\AssistantSchedule::create($pendingSchedules[$hari]);
                    $count++;
                    unset($pendingSchedules[$hari]); // Kosongin buffer
                }
            };

            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue; 

                $seninIndex = array_search('Senin', $row);
                if ($seninIndex !== false) {
                    // Kalau ketemu asisten baru, masukin dulu sisa jadwal asisten sebelumnya ke DB
                    foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h) {
                        $flushSchedule($h);
                    }
                    
                    $currentAssistant = !empty($row[0]) ? $row[0] : (!empty($row[1]) ? $row[1] : 'TBD');
                    continue;
                }

                $jamColumn = '';
                foreach ($row as $col) {
                    if (is_string($col) && str_contains($col, '-') && (str_contains($col, '.') || str_contains($col, ':'))) {
                        $jamColumn = $col;
                        break;
                    }
                }

                if ($jamColumn != '') {
                    $jamRaw = explode('-', $jamColumn);
                    $jamMulai = str_replace('.', ':', trim($jamRaw[0]));
                    $jamSelesai = str_replace('.', ':', trim($jamRaw[1]));

                    $hariMap = [
                        2 => 'Senin',
                        3 => 'Selasa',
                        4 => 'Rabu',
                        5 => 'Kamis',
                        6 => 'Jumat'
                    ];

                    foreach ($hariMap as $colIndex => $namaHari) {
                        $matkul = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';

                        if ($matkul !== '') {
                            // 🚀 CEK APAKAH MATKULNYA BERUNTUN?
                            if (isset($pendingSchedules[$namaHari]) && 
                                $pendingSchedules[$namaHari]['mata_kuliah'] === $matkul && 
                                $pendingSchedules[$namaHari]['nama_asisten'] === trim($currentAssistant)) {
                                
                                // Jika SAMA, jangan bikin baru! Cukup tarik mundur jam selesainya
                                $pendingSchedules[$namaHari]['jam_selesai'] = $jamSelesai;
                            } else {
                                // Jika BEDA Matkul, simpan jadwal yang lama ke DB, mulai catat yang baru
                                $flushSchedule($namaHari);
                                
                                $pendingSchedules[$namaHari] = [
                                    'nama_asisten' => trim($currentAssistant),
                                    'hari'         => $namaHari,
                                    'jam_mulai'    => $jamMulai,
                                    'jam_selesai'  => $jamSelesai,
                                    'mata_kuliah'  => $matkul,
                                ];
                            }
                        } else {
                            // Jika ada jam kosong di tengah-tengah jadwal, tutup jadwal sebelumnya
                            $flushSchedule($namaHari);
                        }
                    }
                }
            }
            
            // Jangan lupa flush sisa buffer terakhir pas sheet-nya habis
            foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h) {
                $flushSchedule($h);
            }
        }

        if ($count === 0) {
            return back()->with('error', 'Gagal! Format tidak dikenali atau file kosong.');
        }

        return back()->with('success', "🚀 Mantap! $count jadwal kuliah asisten berhasil disimpan ke database (Matkul beruntun otomatis digabung!).");
    }

    public function dashboard(Request $request) {
        $labs = Lab::all();
        $countLabs = \App\Models\Lab::count();
        $bookings = Booking::with('user')
                           ->where('status', 'pending')
                           ->latest()
                           ->get();

        $filterDate = $request->query('filter_date', now()->toDateString());

        $schedules = Schedule::whereDate('tanggal', $filterDate)
                           ->orderBy('jam_mulai', 'asc')
                           ->get();
                           
        $query = Schedule::query();

        if ($filterDate) {
            $query->whereDate('tanggal', $filterDate);
        } else {
            $query->whereBetween('tanggal', [now()->toDateString(), now()->addDays(7)->toDateString()]);
        }
        
        $asistenBertugas = Schedule::whereDate('tanggal', $filterDate)
                                ->whereNotNull('nama_asisten')
                                ->where('nama_asisten', '!=', '')
                                ->distinct('nama_asisten')
                                ->count('nama_asisten');

        $asistenHariIni = Schedule::whereDate('tanggal', $filterDate)
                                ->whereNotNull('nama_asisten')
                                ->where('nama_asisten', '!=', '')
                                ->select('nama_asisten', 'lab', 'jam_mulai', 'jam_selesai', 'matkul')
                                ->get();

        return view('spv.dashboard', compact('schedules','labs','filterDate','bookings','query','asistenBertugas','countLabs','asistenHariIni'));
    }

    public function manajemenJadwal(Request $request) {
        $filterDate = $request->query('filter_date', now()->toDateString());

        $schedules = Schedule::whereDate('tanggal', $filterDate)
                           ->orderBy('jam_mulai', 'asc')
                           ->get();
                           
        $query = Schedule::query();

        if ($filterDate) {
            $query->whereDate('tanggal', $filterDate);
        } else {
            $query->whereBetween('tanggal', [now()->toDateString(), now()->addDays(7)->toDateString()]);
        }
        
        $labs = Lab::all();
        return view('spv.jadwal', compact('schedules', 'labs','filterDate'));
    }
    
// API untuk ambil detail asisten via AJAX
    public function getDetailAsisten(Request $request)
    {
        $nama = $request->query('nama');
        
        if (!$nama) {
            return response()->json([]);
        }

        $detail = \App\Models\AssistantSchedule::where('nama_asisten', $nama)
                    ->orderBy('hari', 'asc') // Bisa disesuaikan logic urutannya
                    ->get();

        return response()->json($detail);
    }

    // --- FUNGSI UPDATE INLINE JADWAL ASISTEN ---
    public function updateAsisten(Request $request, $id)
    {
        $asisten = \App\Models\AssistantSchedule::findOrFail($id);
        $jam_mulai = $request->jam_mulai ?? $asisten->jam_mulai;
        $jam_selesai = $asisten->jam_selesai;

        // Jika form mengirimkan input SKS, hitung otomatis jam selesainya
        if ($request->has('sks') && $request->has('jam_mulai')) {
            $menit = $request->sks * 50; // 1 SKS = 50 Menit
            $jam_selesai = date('H:i', strtotime($jam_mulai . " + $menit minutes"));
        }
        
        $asisten->update([
            'nama_asisten' => $request->nama_asisten ?? $asisten->nama_asisten,
            'hari'         => $request->hari ?? $asisten->hari,
            'jam_mulai'    => $request->jam_mulai ?? $asisten->jam_mulai,
            'jam_selesai'  => $request->jam_selesai ?? $asisten->jam_selesai,
            'mata_kuliah'  => $request->mata_kuliah ?? $asisten->mata_kuliah,
        ]);

        return back()->with('success', 'Jadwal asisten berhasil diperbarui!');
    }

    // --- FUNGSI HAPUS JADWAL ASISTEN ---
    public function destroyAsisten($id)
    {
        $asisten = \App\Models\AssistantSchedule::findOrFail($id);
        $asisten->delete();

        return back()->with('success', 'Jadwal asisten berhasil dihapus!');
    }
    // ✅ NAMA FUNGSI SUDAH DIBENARKAN & DITAMBAHKAN QUERY HISTORI
    public function aproveBoking(Request $request) {
        // 1. Data untuk yang masih PENDING
        $bookings = Booking::with('user')
                           ->where('status', 'pending')
                           ->latest()
                           ->get();
                           
        // 2. Data HISTORI (Approved & Rejected)
        $histories = Booking::with('user')
                           ->whereIn('status', ['approved', 'rejected'])
                           ->orderBy('updated_at', 'desc')
                           ->limit(10)
                           ->get();
                           
        $asistenSchedules = \App\Models\AssistantSchedule::all();
        
        // 3. LOGIKA PEMISAH STATISTIK
        $totalOrmawa = $bookings->filter(function($b) {
            return !empty($b->user->nama_ormawa);
        })->count();

        $totalDosen = $bookings->filter(function($b) {
            return empty($b->user->nama_ormawa);
        })->count();
        
        // Pastikan melempar $histories ke Blade
        return view('spv.aprove', compact('asistenSchedules','bookings','totalDosen','totalOrmawa', 'histories'));
    }

    public function jadwalAsisten(Request $request) {
        // Ambil semua data unik untuk dropdown pencarian nama
        $semuaAsisten = \App\Models\AssistantSchedule::select('nama_asisten')->distinct()->get();
        
        // Tangkap parameter dari URL
        $namaDicari = $request->query('nama');
        $hariDicari = $request->query('hari');

        // Jika ada filter yang dipilih (Nama ATAU Hari)
        if ($namaDicari || $hariDicari) {
            $query = \App\Models\AssistantSchedule::query();
            
            // Filter by Nama (Jika dipilih)
            if ($namaDicari) {
                $query->where('nama_asisten', $namaDicari);
            }
            
            // Filter by Hari (Jika dipilih)
            if ($hariDicari) {
                $query->where('hari', $hariDicari);
            }
            
            // 🚀 LOGIKA SORTING SAKTI: 
            // 1. Urutkan berdasarkan Hari sesungguhnya (Senin -> Sabtu)
            // 2. Lalu urutkan berdasarkan Jam Mulai (Pagi -> Sore)
            $asistenSchedules = $query->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
                                      ->orderBy('jam_mulai', 'asc')
                                      ->get();
        } else {
            // Jika tidak ada filter sama sekali, kosongkan tabel
            $asistenSchedules = collect([]); 
        }

        return view('spv.asisten', compact('semuaAsisten', 'asistenSchedules', 'namaDicari', 'hariDicari'));
    }

// --- FUNGSI SAPU BERSIH (RESET) JADWAL ASISTEN ---
    public function clearAsistenSchedule()
    {
        // Truncate akan menghapus semua isi tabel dan mengembalikan ID kembali ke 1
        \App\Models\AssistantSchedule::truncate();

        return back()->with('success', '🧹 Wusss! Semua data jadwal asisten berhasil disapu bersih.');
    }

    // --- FUNGSI TAMBAH JADWAL ASISTEN BARU ---
    public function storeAsisten(Request $request)
    {
        $request->validate([
            'nama_asisten' => 'required|string',
            'hari'         => 'required|string',
            'jam_mulai'    => 'required',
            'sks'          => 'required|numeric', // Validasi SKS
            'mata_kuliah'  => 'required|string',
        ]);

        // Hitung jam selesai berdasarkan SKS
        $menit = $request->sks * 50;
        $jam_selesai = date('H:i', strtotime($request->jam_mulai . " + $menit minutes"));

        \App\Models\AssistantSchedule::create([
            'nama_asisten' => $request->nama_asisten,
            'hari'         => $request->hari,
            'jam_mulai'    => $request->jam_mulai,
            'jam_selesai'  => $jam_selesai,
            'mata_kuliah'  => $request->mata_kuliah,
        ]);

        return back()->with('success', 'Jadwal mata kuliah baru berhasil ditambahkan!');
    }
}

