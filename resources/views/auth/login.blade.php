<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Laboratorium</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <img src="{{ asset('img/logo-ubl.png') }}" alt="Logo" class="brand-logo" onerror="this.style.display='none'">
                    <h2>Selamat Datang</h2>
                    <p>Silakan login untuk mengakses sistemnya danang</p>
                </div>

                @if ($errors->any())
                    <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                        @foreach ($errors->all() as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="login_id">Email (Mhs/Dosen) / NIP (Dosen)</label>
                        <input type="text" id="login_id" name="login_id" value="{{ old('login_id') }}" required autofocus placeholder="Masukkan Email atau NIP">
                    </div>

                    <div class="form-group">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <label for="password">Password</label>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" style="font-size: 12px; color: var(--primary-blue); text-decoration: none;">Lupa Password?</a>
        @endif
    </div>
    
    <div style="position: relative;">
        <input type="password" id="password" name="password" required placeholder="" style="padding-right: 40px; width: 100%;">
        
        <span id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;">
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </span>
    </div>
</div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="remember" name="remember" style="width: auto; cursor: pointer;">
                        <label for="remember" style="margin-bottom: 0; cursor: pointer; color: var(--text-muted);">Ingat saya di perangkat ini</label>
                    </div>

                    <button type="submit" class="btn-primary">
                        Login
                    </button>
                </form>

                <div class="auth-footer">
                    Belum punya akun? <a href="{{ route('register') }}">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    </div>
</body>
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
</html>