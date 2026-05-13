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

@section('title', 'Manajemen Lab')

@section('content')
{{-- Panggil CSS --}}
<link rel="stylesheet" href="{{ asset('css/spv-lab.css') }}">

<div class="lab-management-wrapper">
    <div class="lab-header">
        <h2>Dashboard Manajemen Laboratorium</h2>
        <p>Gunakan panel ini untuk memonitor dan menambah kapasitas infrastruktur.</p>
    </div>

    <div class="lab-grid">
        {{-- Sisi Kiri: Form --}}
        <div class="lab-form-card">
            <h3>➕ Tambah Lab</h3>
            <form action="{{ route('lab.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Identitas / Nama Lab</label>
                    <input type="text" name="nm_lab" placeholder="Misal: Lab Riset AI" required>
                </div>
                <div class="form-group">
                    <label>Kapasitas Mahasiswa</label>
                    <input type="number" name="kapasitas" placeholder="Contoh: 40" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi Fasilitas</label>
                    <textarea name="fasilitas" rows="4" placeholder="Sebutkan PC, AC, Proyektor, dll..."></textarea>
                </div>
                <button type="submit" style="width:100%; padding:14px; background:var(--primary); color:white; border:none; border-radius:10px; font-weight:700; cursor:pointer;">
                    Simpan Data Lab
                </button>
            </form>
        </div>

        {{-- Sisi Kanan: Daftar --}}
        <div class="lab-list-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin:0; border:none; padding:0;">📦 Daftar Lab Tersedia</h3>
                <span style="background: #e0f2fe; color: #0369a1; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 700;">
                    Total: {{ $labs->count() }} Lab
                </span>
            </div>

            <div class="lab-cards-container">
                @foreach ($labs as $lab)
                <div class="lab-item-card">
                    <div class="lab-item-header">
                        <h4>{{ $lab->nm_lab }}</h4>
                    </div>
                    <div class="lab-item-body">
                        <p><strong>👥 Kapasitas:</strong> {{ $lab->kapasitas }} Orang</p>
                        <p><strong>🛠️ Fasilitas:</strong> {{ Str::limit($lab->fasilitas, 100) }}</p>
                    </div>
                    <div style="margin-top:20px; display:flex; gap:10px;">
                        {{-- Tombol Edit Modal --}}
                        <button class="btn-edit" style="flex:1; padding:8px; border-radius:6px; border:1px solid #ddd; background:#fff; cursor:pointer;" onclick="openEditModal({{ json_encode($lab) }})">Edit</button>
                        
                        <form action="{{ route('lab.destroy', $lab->id) }}" method="POST" style="flex:1;">
                            @csrf @method('DELETE')
                            <button type="submit" style="width:100%; padding:8px; border-radius:6px; border:none; background:#fee2e2; color:#b91c1c; font-weight:600; cursor:pointer;" onclick="return confirm('Hapus lab ini?')">Hapus</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="modalEditLab" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div class="modal-content" style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px;">
        <h3 style="margin-top:0;">Edit Data Laboratorium</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama Lab</label>
                <input type="text" name="nm_lab" id="edit_nm_lab" required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>Kapasitas</label>
                <input type="number" name="kapasitas" id="edit_kapasitas" required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;">
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>Fasilitas</label>
                <textarea name="fasilitas" id="edit_fasilitas" rows="3" required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ccc;"></textarea>
            </div>
            <div class="modal-actions" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="closeEditModal()" style="flex: 1; padding: 10px; background: #eee; border: none; border-radius: 6px; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-submit" style="flex: 1; padding: 10px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(lab) {
        // Isi nilai input modal dengan data dari tombol yang diklik
        document.getElementById('edit_nm_lab').value = lab.nm_lab;
        document.getElementById('edit_kapasitas').value = lab.kapasitas;
        document.getElementById('edit_fasilitas').value = lab.fasilitas;
        
        // PENTING: Atur action form menggunakan awalan /spv agar tidak 404
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
