

@section('title', 'Pembuatan Akun')
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun - Lab Budi Luhur</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    {{-- Panggil kerangka master tadi --}}
@extends('layouts.spv')

@section('title', 'Manajemen Akun')

@section('content')
<link rel="stylesheet" href="{{ asset('css/spv-lab.css') }}">

<div class="lab-management-wrapper">
    <div class="lab-header">
        <h2>Dashboard Manajemen Pengguna</h2>
        <p>Kelola pembuatan akun baru dan pantau daftar pengguna terdaftar.</p>
    </div>

    @if ($errors->any())
        <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <strong style="display: block; margin-bottom: 5px;">⚠️ Gagal Menyimpan:</strong>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif
    
    @if(session('success'))
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="lab-grid">
        {{-- ================= SISI KIRI: FORM BUAT AKUN ================= --}}
        <div class="lab-form-card">
            <h3>➕ Buat Akun Baru</h3>
            <form action="{{ route('register') }}" method="POST" id="reg-form">
                @csrf
                <div class="form-group">
                    <label>Daftar Sebagai:</label>
                    <select name="role" id="role-select" class="form-input" required onchange="toggleRoleForm()">
                        <option value="dosen" selected>Dosen</option>
                        <option value="ormawa">Organisasi Mahasiswa (ORMAWA)</option>
                        <option value="spv">Supervisor (SPV)</option>
                    </select>
                </div>

                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

                {{-- Dinamis: Dosen --}}
                <div id="form-dosen" class="role-section">
                    <div class="form-group">
                        <label>Nama Lengkap & Gelar</label>
                        <input type="text" name="name_dosen" class="form-input" placeholder="Contoh: Danang, M.Kom">
                    </div>
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" name="nip" class="form-input" placeholder="Masukkan NIP">
                    </div>
                </div>

                {{-- Dinamis: ORMAWA --}}
                <div id="form-ormawa" class="role-section" style="display: none;">
                    <div class="form-group">
                        <label>Nama Perwakilan</label>
                        <input type="text" name="name_ormawa" class="form-input" placeholder="Nama penanggung jawab">
                    </div>
                    <div class="form-group">
                        <label>Nama Organisasi</label>
                        <input type="text" name="nama_organisasi" class="form-input" placeholder="Contoh: HIMASI">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi/Latar Belakang</label>
                        <textarea name="deskripsi" rows="2" class="form-input" placeholder="Singkat mengenai organisasi"></textarea>
                    </div>
                </div>
                    
                {{-- Dinamis: SPV --}}
                <div id="form-spv" class="role-section" style="display: none;">
                    <div class="form-group">
                        <label>Nama Lengkap SPV</label>
                        <input type="text" name="name_spv" class="form-input" placeholder="Masukkan nama SPV">
                    </div>
                </div>

                {{-- Input Global --}}
                <div class="form-group">
                    <label>No WA Aktif</label>
                    <input type="text" name="no_wa" class="form-input" placeholder="Contoh: 081234567890" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-input" required placeholder="@budiluhur.ac.id">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-input" required placeholder="Minimal 8 karakter">
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-input" required placeholder="Ulangi password">
                </div>

                <button type="submit" style="width:100%; padding:14px; background:var(--primary); color:white; border:none; border-radius:10px; font-weight:700; cursor:pointer;">
                    Daftarkan Akun
                </button>
            </form>
        </div>

        {{-- ================= SISI KANAN: DAFTAR AKUN ================= --}}
        <div class="lab-list-section">
            <h3 style="margin-top:0;">👥 Daftar Akun Sistem</h3>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0; color: #64748b;">
                            <th style="padding: 12px 8px;">Pengguna</th>
                            <th style="padding: 12px 8px;">Role</th>
                            <th style="padding: 12px 8px;">Email & WA</th>
                            <th style="padding: 12px 8px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Ini akan mengambil data $users dari controller --}}
                        @if(isset($users))
                            @forelse($users as $user)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 8px;">
                                    <strong style="color: #0f172a;">{{ $user->name }}</strong><br>
                                    <small style="color: #64748b;">
                                        {{ $user->role == 'dosen' ? 'NIP: ' . $user->nip : ($user->role == 'ormawa' ? 'Org: ' . $user->nama_organisasi : 'SPV') }}
                                    </small>
                                </td>
                                <td style="padding: 12px 8px;">
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td style="padding: 12px 8px; font-size: 13px;">
                                    {{ $user->email }}<br>
                                    <span style="color: #10b981;">{{ $user->no_wa }}</span>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <button onclick="lihatDetail({{ json_encode($user) }})" style="padding: 6px 12px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; font-weight: 600; color: #334155; transition: 0.2s;">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" style="text-align: center; padding: 20px;">Belum ada pengguna terdaftar.</td></tr>
                            @endforelse
                        @else
                            <tr><td colspan="4" style="text-align: center; padding: 20px; color:red;">Variabel $users belum dikirim dari Controller!</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL DETAIL AKUN ================= --}}
<div id="modalDetailUser" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div class="modal-content" style="background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 450px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
            <h3 style="margin:0; color: var(--primary);">📋 Detail Pengguna</h3>
            <button onclick="tutupDetail()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #ef4444;">&times;</button>
        </div>
        
        <div id="detailContent" style="font-size: 14px; line-height: 1.8; color: #334155;">
            </div>

        <div style="margin-top: 25px; background: #fffbeb; padding: 12px; border-left: 4px solid #f59e0b; border-radius: 4px; font-size: 12px;">
            <strong>💡 Info Keamanan:</strong> Password telah dienkripsi secara aman oleh sistem dan tidak dapat dilihat. Gunakan fitur "Lupa Password" jika pengguna tidak bisa login.
        </div>
        
        <button onclick="tutupDetail()" style="width: 100%; padding: 12px; margin-top: 20px; background: #e2e8f0; color: #1e293b; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
            Tutup
        </button>
    </div>
</div>

<script>
    // JS Untuk Ganti Form Role
    function toggleRoleForm() {
        const role = document.getElementById('role-select').value;
        document.getElementById('form-dosen').style.display = role === 'dosen' ? 'block' : 'none';
        document.getElementById('form-ormawa').style.display = role === 'ormawa' ? 'block' : 'none';
        document.getElementById('form-spv').style.display = role === 'spv' ? 'block' : 'none';
    }

    // JS Untuk Buka Modal Detail
    function lihatDetail(user) {
        let extraInfo = '';
        if (user.role === 'dosen') extraInfo = `<b>NIP:</b> ${user.nip || '-'}`;
        if (user.role === 'ormawa') extraInfo = `<b>Organisasi:</b> ${user.nama_organisasi || '-'}<br><b>Deskripsi:</b> ${user.deskripsi || '-'}`;

        const html = `
            <div style="margin-bottom: 10px;"><b>Nama Lengkap:</b> <span style="color:#000;">${user.name}</span></div>
            <div style="margin-bottom: 10px;"><b>Hak Akses:</b> <span style="text-transform:uppercase;">${user.role}</span></div>
            <div style="margin-bottom: 10px;"><b>Email:</b> ${user.email}</div>
            <div style="margin-bottom: 10px;"><b>No. WhatsApp:</b> ${user.no_wa}</div>
            <div style="margin-bottom: 10px;">${extraInfo}</div>
            <div style="margin-top: 15px; font-size: 12px; color: #94a3b8;"><b>Mendaftar Pada:</b> ${new Date(user.created_at).toLocaleString('id-ID')}</div>
        `;
        
        document.getElementById('detailContent').innerHTML = html;
        document.getElementById('modalDetailUser').style.display = 'flex';
    }

    function tutupDetail() {
        document.getElementById('modalDetailUser').style.display = 'none';
    }
</script>
@endsection
</body>
</html>