<?php
namespace App\Http\Controllers;

use App\Models\Lab; // Pastikan model Lab sudah ada
use Illuminate\Http\Request;

class LabController extends Controller
{
    public function store(Request $request)
    {
        // Validasi agar data yang masuk ke atribut nm_lab tidak kosong
        $request->validate([
            'nm_lab' => 'required|unique:labs,nm_lab',
        ]);

        // Simpan ke database. Laravel otomatis kasih Primary Key (#) [cite: 52]
        Lab::create([
            'nm_lab' => $request->nm_lab
        ]);

        return back()->with('success', 'Lab baru ('. $request->nm_lab .') berhasil ditambah!');
    }

    public function destroy($id)
    {
        // Hapus berdasarkan Primary Key (#) [cite: 52]
        Lab::findOrFail($id)->delete();
        return back()->with('success', 'Lab berhasil dihapus!');
    }
}