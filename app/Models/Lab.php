<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    use HasFactory;

    protected $fillable = 
    
    ['nm_lab', 'kapasitas', 'fasilitas'];

    // TAMBAHKAN INI (Kunci untuk memanggil nama user)
    
}