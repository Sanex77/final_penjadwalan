<?php

namespace App\Http\Controllers;

use App\Models\User; // Pastikan Model User dipanggil di atas
use Illuminate\Http\Request;

class UserController extends Controller 
{
    public function manajemenAkun()
    {
        // 1. Ambil semua data pengguna dari database
        $users = User::orderBy('created_at', 'desc')->get();
        
        // 2. Kirim data $users ke halaman (view) akun.blade.php
        return view('spv.akun', compact('users'));
    }
}