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
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <img src="{{ asset('img/logo-ubl.png') }}" alt="Logo UBL" class="brand-logo">
                    <h2>Buat Akun Baru</h2>
                    <p>Silakan lengkapi data diri Anda di bawah ini</p>
                </div>
                            @if ($errors->any())
    <div style="background-color: #ffe6e6; border-left: 5px solid #ff4d4d; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
        <strong style="color: #b30000;">Waduh, ada yang salah nih:</strong>
        <ul style="color: #b30000; margin-top: 5px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                <form action="{{ route('register') }}" method="POST" id="reg-form">
                    @csrf
                    
                    <div class="form-group">
                        <label for="role-select">Daftar Sebagai:</label>
                        <select name="role" id="role-select" class="form-select" required>
                            <option value="dosen" selected>Dosen</option>
                            <option value="ormawa">Organisasi Mahasiswa (ORMAWA)</option>
                            <option value="spv">Supervisor (SPV)</option>
                        </select>
                    </div>

                    <hr class="form-divider">

                    <div id="form-dosen" class="role-section">
                        <div class="form-group">
                            <label>Nama Lengkap & Gelar</label>
                            <input type="text" name="name_dosen" placeholder="Contoh: Danang, S.Kom., M.Kom">
                        </div>
                        <div class="form-group">
                            <label>NIP</label>
                            <input type="text" name="nip" placeholder="Masukkan NIP">
                        </div>
                        <div class="form-group">
                            <label>No WA Aktif</label>
                            <input type="text" name="no_wa" placeholder="Contoh: 081234567890" pattern="[0-9+ \-]*" oninput="this.value = this.value.replace(/[^0-9+\- ]/g, '');" required>
                        </div>
                    </div>

                    <div id="form-ormawa" class="role-section" style="display: none;">
                        <div class="form-group">
                            <label>Nama Perwakilan</label>
                            <input type="text" name="name_ormawa" placeholder="Nama penanggung jawab">
                        </div>
                        <div class="form-group">
                            <label>Nama Organisasi</label>
                            <input type="text" name="nama_organisasi" placeholder="Contoh: HIMASI / KMM">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi/Latar Belakang</label>
                            <textarea name="deskripsi" rows="2" placeholder="Singkat mengenai organisasi"></textarea>
                        </div>
                       <div class="form-group">
                            <label>No WA Aktif</label>
                            <input type="text" name="no_wa" placeholder="Contoh: 081234567890" pattern="[0-9+ \-]*" oninput="this.value = this.value.replace(/[^0-9+\- ]/g, '');" required>
                        </div>
                    </div>
                        
                    <div id="form-spv" class="role-section" style="display: none;">
                        <div class="form-group">
                            <label>Nama Lengkap SPV</label>
                            <input type="text" name="name_spv" placeholder="Masukkan nama lengkap">
                            
                        </div>

                        <div class="form-group">
                            <label>No WA Aktif</label>
                            <input type="text" name="no_wa" placeholder="Contoh: 081234567890" pattern="[0-9+ \-]*" oninput="this.value = this.value.replace(/[^0-9+\- ]/g, '');" required>
                        </div>
                        
                    </div>
                
                


                    <div class="form-group">
                        <label id="label-email">Email</label>
                        <input type="email" name="email" required placeholder="@budiluhur.ac.id/@student.budiluhur.ac.id">
                        <small id="email-hint" class="text-muted">*Gunakan email aktif budiluhur untuk verifikasi akun</small>
                    </div>

                    

                    <div class="form-group">
                        <label>Password</label>
                        <div style="position: relative;">
        <input type="password" id="password" name="password" required placeholder="" style="padding-right: 40px; width: 100%;">
        
        <span id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;">
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </span>
    </div>
            

                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required placeholder="Ulangi password">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Daftar Sekarang</button>
                        <div class="auth-footer">
                            Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/register.js') }}"></script>
    <script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function (e) {
        // 1. Toggle tipe input
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // 2. Ganti warna ikon kalau lagi "telanjang" biar user tahu
        if (type === 'text') {
            this.style.color = 'var(--primary-blue)'; // Warna pas lagi kelihatan
            // Opsional: Ganti gambar SVG ke mata dicoret kalau kamu punya
        } else {
            this.style.color = '#666';
        }
    });
</script>
</body>
</html>