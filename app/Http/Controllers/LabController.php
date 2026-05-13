<?php
namespace App\Http\Controllers;

use App\Models\Lab; // Pastikan model Lab sudah ada
use Illuminate\Http\Request;

class LabController extends Controller
{   
    public function index()
    {
        $labs = Lab::all(); 
        return view('spv.lab', compact('labs')); 
    }
    

    public function store(Request $request)
    {
        // Validasi agar data yang masuk ke atribut nm_lab tidak kosong
        $request->validate([
            'nm_lab' => 'required|unique:labs,nm_lab',
            'kapasitas' => 'required|integer|min:0',
            'fasilitas' => 'required|string'
        ]);



        // Simpan ke database. Laravel otomatis kasih Primary Key (#) [cite: 52]
        Lab::create([
            'nm_lab' => $request->nm_lab,
            'kapasitas' => $request->kapasitas,
            'fasilitas' => $request->fasilitas
        ]);

        return back()->with('success', 'Lab baru ('. $request->nm_lab .') berhasil ditambah!');
    }
    public function update(Request $request, $id)
{
    // 1. Validasi data
    $request->validate([
        'nm_lab' => 'required|unique:labs,nm_lab,' . $id,
        'kapasitas' => 'required|integer',
        'fasilitas' => 'required|string'
    ]);

    // 2. Cari data lab berdasarkan ID
    $lab = Lab::findOrFail($id);

    // 3. Update data di database
    $lab->update([
        'nm_lab' => $request->nm_lab,
        'kapasitas' => $request->kapasitas,
        'fasilitas' => $request->fasilitas,
    ]);

    // 4. Kembali ke halaman sebelumnya dengan pesan sukses
    return back()->with('success', 'Data lab berhasil diperbarui!');
}

    public function destroy($id)
    {
        // Hapus berdasarkan Primary Key (#) [cite: 52]
        Lab::findOrFail($id)->delete();
        return back()->with('success', 'Lab berhasil dihapus!');
    }
}