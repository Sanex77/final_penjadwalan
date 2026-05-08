<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistantSchedule extends Model
{
    use HasFactory;

    // Opsional: Kasih tau Laravel nama tabel pastinya (kalau takut salah tebak)
    protected $table = 'assistant_schedules';

    // WAJIB: Izinkan kolom-kolom ini diisi secara massal lewat Controller
    protected $fillable = [
        'nama_asisten',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'mata_kuliah',
    ];
}