<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth; // <-- Ini udah gak dipakai, aman dihapus

class RegisteredUserController extends Controller
{
    public function create() // <-- Hapus ': view' biar gak error di PHP tertentu
    {
        // Pastikan nama file-nya adalah resources/views/spv/akun.blade.php
        return view('spv.akun');
    }

    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required',
            'email' => [
                'required',
                'email',
                'unique:users',
                // LOGIKA TAMBAHAN: Validasi Domain Email
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->role === 'dosen') {
                        if (!str_ends_with($value, '@budiluhur.ac.id')) {
                            $fail('Dosen wajib menggunakan email resmi @budiluhur.ac.id');
                        }
                    } elseif ($request->role === 'ormawa') {
                        if (!str_ends_with($value, '@student.budiluhur.ac.id')) {
                            $fail('Ormawa wajib menggunakan email @student.budiluhur.ac.id');
                        }
                    }
                },
            ],
            'password' => 'required|confirmed|min:6',
            'no_wa' => ['required', 'string', 'max:20'],
        ]);

        $name = $request->name_dosen 
            ?? $request->name_ormawa 
            ?? $request->name_spv;

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => bcrypt($request->password), // <-- WAJIB pakai bcrypt() atau Hash::make() biar bisa login!
            'role' => $request->role,
            'nip' => $request->nip,
            'no_wa' => $request->no_wa,
            'nama_ormawa' => $request->nama_organisasi,
            'deskripsi' => $request->deskripsi,
        ]);

        event(new Registered($user));
        
        // 🚨 Auth::login($user); SUDAH SAYA HAPUS BIAR SPV GAK KE-LOGOUT!

        // Pastikan action di tag <form> kamu mengarah ke route('spv.akun.submit')
        return redirect()->route('spv.akun')->with('success', 'Berhasil! Akun ' . $user->name . ' telah didaftarkan sebagai ' . strtoupper($user->role) . '.');
    }
}