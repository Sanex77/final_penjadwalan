<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Di sini kita kasih "Izin Masuk" untuk semua kolom yang mau diisi SPV
#[Fillable([
    'tanggal', 
    'hari', 
    'lab', 
    'jam_mulai', 
    'jam_selesai', 
    'matkul', 
    'sks', 
    'dosen',
    'nama_asisten'
])]
class Schedule extends Model
{
    use HasFactory;

    /**
     * Tips: Kamu bisa menambahkan casts kalau ingin jam_mulai 
     * dan jam_selesai otomatis dianggap sebagai objek waktu.
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }
    public function getLabStatuses()
    {
        $allLabs = \App\Models\Lab::all();
        $statuses = [];

        foreach ($allLabs as $lab) {
            // Cek apakah lab ini dipakai oleh jadwal LAIN (id berbeda) 
            // di tanggal dan irisan jam yang sama
            $isBusy = \App\Models\Schedule::where('tanggal', $this->tanggal)
                ->where('id', '!=', $this->id) // Abaikan diri sendiri
                ->where('lab', $lab->nm_lab)
                ->where(function($query) {
                    $query->where('jam_mulai', '<', $this->jam_selesai)
                          ->where('jam_selesai', '>', $this->jam_mulai);
                })->exists();

            $statuses[] = [
                'nm_lab' => $lab->nm_lab,
                'status' => $isBusy ? 'busy' : 'available'
            ];
        }

        return $statuses;
    }

    // --- TAMBAHKAN FUNGSI INI ---
   public function getAssistantStatuses()
{
    // 1. Ambil semua nama asisten unik
    $allAssistants = \App\Models\AssistantSchedule::distinct()->pluck('nama_asisten');

    // Ambil variabel waktu jadwal saat ini
    $hariTarget = $this->hari;
    $tanggalTarget = $this->tanggal;
    $mulaiTarget = $this->jam_mulai;
    $selesaiTarget = $this->jam_selesai;

    // 2. CARI ASISTEN YANG SEDANG KULIAH (Bentrok Kuliah)
    // Ubah: get() nama & mata_kuliah, lalu jadikan 'nama_asisten' sebagai kunci (keyBy)
    $busyWithClass = \App\Models\AssistantSchedule::where('hari', $hariTarget)
        ->where(function($query) use ($mulaiTarget, $selesaiTarget) {
            $query->where('jam_mulai', '<', $selesaiTarget)
                  ->where('jam_selesai', '>', $mulaiTarget);
        })
        ->get(['nama_asisten', 'mata_kuliah'])
        ->keyBy('nama_asisten');

    // 3. CARI ASISTEN YANG SUDAH DITUGASKAN DI LAB LAIN (Double Booking)
    // Ubah: get() nama & lab, lalu jadikan 'nama_asisten' sebagai kunci
    $busyInOtherLab = \App\Models\Schedule::where('tanggal', $tanggalTarget)
        ->where('id', '!=', $this->id) // Abaikan baris ini sendiri
        ->whereNotNull('nama_asisten')
        ->where(function($query) use ($mulaiTarget, $selesaiTarget) {
            $query->where('jam_mulai', '<', $selesaiTarget)
                  ->where('jam_selesai', '>', $mulaiTarget);
        })
        ->get(['nama_asisten', 'lab'])
        ->keyBy('nama_asisten');

    // 4. Mapping Statusnya
    return $allAssistants->map(function ($nama) use ($busyWithClass, $busyInOtherLab) {
        $status = 'available';
        $label = '';

        // Cek apakah nama asisten ada di daftar yang lagi kuliah
        if ($busyWithClass->has($nama)) {
            $status = 'busy_class';
            $matkul = $busyWithClass->get($nama)->mata_kuliah;
            $label = "(Kuliah: {$matkul})"; // Tampilkan nama matkulnya!
        } 
        // Cek apakah nama asisten ada di daftar yang lagi jaga lab lain
        elseif ($busyInOtherLab->has($nama)) {
            $status = 'busy_lab';
            $lab = $busyInOtherLab->get($nama)->lab;
            $label = "(Jaga: {$lab})"; // Tampilkan nama/kode labnya!
        }

        return (object) [
            'nama' => $nama,
            'is_busy' => ($status !== 'available'),
            'label' => $label
        ];
    });
}

}