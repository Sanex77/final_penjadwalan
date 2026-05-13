<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/spv-lab.css') }}">
    
    <title>Data Lab</title>
</head>
<body>
@extends('layouts.spv')

@section('title', 'Manajemen Laboratorium')

@section('content')
<div class="lab-management-wrapper">
    <div class="lab-header">
        <h2>Manajemen Laboratorium</h2>
        <p>Kelola penambahan data dan fasilitas lab yang tersedia.</p>
    </div>

    <div class="lab-grid">
        {{-- Sisi Kiri: Form Tambah Lab --}}
        <div class="lab-form-card">
            <h3>Tambah Lab Baru</h3>
            <form action="{{ route('lab.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nama Lab</label>
                    <input type="text" name="nm_lab" value="{{ old('nm_lab') }}" placeholder="Contoh: Lab 01" required>
                
                <div class="form-group">
                    <label>Kapasitas (Orang)</label>
                    <input type="number" name="kapasitas" value="{{ old('kapasitas') }}" placeholder="Contoh: 40" required>
                </div>
                
                <div class="form-group">
                    <label>Fasilitas Utama</label>
                    <textarea name="fasilitas" rows="3" value="{{ old('fasilitas') }}" placeholder="Contoh: 40 PC, 2 AC, Proyektor..."></textarea>
                </div>
                
                <button type="submit" class="btn-submit">Simpan Lab</button>
            </form>
        </div>

        {{-- Sisi Kanan: List Lab yang sudah ada --}}
        {{-- Sisi Kanan: List Lab dari Database --}}
<div class="lab-list-section">
    <h3>Daftar Laboratorium</h3>
    <div class="lab-cards-container">
        @forelse ($labs as $lab)
            <div class="lab-item-card">
                <div class="lab-item-header">
                    <h4>{{ $lab->nm_lab }}</h4>
                    <span class="badge active">Aktif</span>
                </div>
                <div class="lab-item-body">
                    <p><strong>Kapasitas:</strong> {{ $lab->kapasitas }} Orang</p>
                    <p><strong>Fasilitas:</strong> {{ $lab->fasilitas }}</p>
                </div>
                <div class="lab-item-actions">
                    {{-- TOMBOL EDIT: Memanggil fungsi JS dengan data lab --}}
                    <button class="btn-edit" onclick="openEditModal({{ json_encode($lab) }})">Edit</button>
                    
                    <form action="{{ route('lab.destroy', $lab->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <p>Belum ada data lab.</p>
        @endforelse
    </div>
</div>

{{-- ================= MODAL EDIT (Hidden by default) ================= --}}
<div id="modalEditLab" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h3>Edit Data Laboratorium</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama Lab</label>
                <input type="text" name="nm_lab" id="edit_nm_lab" required>
            </div>
            <div class="form-group">
                <label>Kapasitas</label>
                <input type="number" name="kapasitas" id="edit_kapasitas" required>
            </div>
            <div class="form-group">
                <label>Fasilitas</label>
                <textarea name="fasilitas" id="edit_fasilitas" rows="3" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
    </div>
</div>
@endsection

{{-- ================= BAGIAN JAVASCRIPT ================= --}}
@section('scripts')
<script>
    // Fungsi untuk membuka modal
    function openModal() {
        document.getElementById('modalTambahLab').style.display = 'flex';
    }

    // Fungsi untuk menutup modal
    function closeModal() {
        document.getElementById('modalTambahLab').style.display = 'none';
    }

    // Tutup modal kalau user klik di luar kotak putih
    window.onclick = function(event) {
        var modal = document.getElementById('modalTambahLab');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function openEditModal(lab) {
        // Isi nilai input modal dengan data dari tombol yang diklik
        document.getElementById('edit_nm_lab').value = lab.nm_lab;
        document.getElementById('edit_kapasitas').value = lab.kapasitas;
        document.getElementById('edit_fasilitas').value = lab.fasilitas;
        
        // Atur action form secara dinamis ke route update
        const form = document.getElementById('editForm');
        form.action = `/lab/${lab.id}`; 
        
        // Tampilkan modal
        document.getElementById('modalEditLab').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('modalEditLab').style.display = 'none';
    }
</script>
@endsection
</body>
</html>
