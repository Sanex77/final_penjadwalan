<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'hari',
        'lab',
        'jam_mulai',
        'jam_selesai',
        'keperluan',
        'sks',
        'file_surat',
        'status',
        'nama_ormawa',
        'kapasitas'
    ];

    // TAMBAHKAN INI (Kunci untuk memanggil nama user)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // File: app/Models/Booking.php

public function lab_relation()
{
    // Kita sambungkan kolom 'lab' di booking dengan 'nm_lab' di tabel Lab
    return $this->belongsTo(Lab::class, 'lab', 'nm_lab');
}

// File: app/Models/Booking.php

public function getLabStatuses()
{
    $allLabs = \App\Models\Lab::all();
    $labData = [];

    foreach ($allLabs as $lab) {
        // 1. Cek di Jadwal Tetap (Schedules)
        $schedule = \App\Models\Schedule::where('tanggal', $this->tanggal)
            ->where('lab', $lab->nm_lab)
            ->where(function($query) {
                $query->where('jam_mulai', '<', $this->jam_selesai)
                      ->where('jam_selesai', '>', $this->jam_mulai);
            })->first();

        if ($schedule) {
            $labData[] = [
                'nm_lab'  => $lab->nm_lab,
                'status'  => 'busy',
                'info'    => $schedule->matkul // Contoh: "TSBD (AC)"
            ];
            continue;
        }

        // 2. Cek di Booking lain yang sudah Approved
        $booking = \App\Models\Booking::where('tanggal', $this->tanggal)
            ->where('lab', $lab->nm_lab)
            ->where('status', 'approved')
            ->where(function($query) {
                $query->where('jam_mulai', '<', $this->jam_selesai)
                      ->where('jam_selesai', '>', $this->jam_mulai);
            })->first();

        if ($booking) {
            $labData[] = [
                'nm_lab'  => $lab->nm_lab,
                'status'  => 'busy',
                'info'    => $booking->keperluan // Contoh: "Rapat HIMASI"
            ];
            continue;
        }

        // 3. Lab Kosong
        $labData[] = [
            'nm_lab'  => $lab->nm_lab,
            'status'  => 'free',
            'info'    => 'Tersedia'
        ];
    }

    return $labData;
}
}