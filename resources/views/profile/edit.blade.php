<x-app-layout>
    <head>
        <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    </head>

    <div class="profile-page" style="flex-direction: column; gap: 40px; padding: 50px 0;">
        
        <div style="text-align: center; margin-bottom: 10px;">
            <h1 style="color: var(--ubl-blue); font-weight: 800; font-size: 32px;">Pengaturan Akun</h1>
            <p style="color: #666;">Semua pengaturan akun Lab Budi Luhur kamu ada di sini.</p>
        </div>

        <section class="profile-card">
            <header class="profile-header">
                <h2>Informasi Profil</h2>
                <p>Perbarui nama dan nomor WhatsApp aktif Anda.</p>
            </header>

            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required autofocus>
                    @if($errors->has('name'))
                        <small style="color: red;">{{ $errors->first('name') }}</small>
                    @endif
                </div>

                <div class="form-group">
                    <label>Email (Terkunci)</label>
                    <input type="email" class="form-control form-control-readonly" value="{{ $user->email }}" readonly>
                </div>

                <div class="form-group">
                    <label>Nomor WhatsApp</label>
                    <input type="text" name="no_wa" class="form-control" value="{{ old('no_wa', $user->no_wa) }}" required>
                    @if($errors->has('no_wa'))
                        <small style="color: red;">🚨 {{ $errors->first('no_wa') }}</small>
                    @endif
                </div>

                <button type="submit" class="btn-save">Simpan Perubahan Profil</button>
                @if (session('status') === 'profile-updated')
                    <p class="status-msg">✅ Profil berhasil diperbarui!</p>
                @endif
            </form>
        </section>

        <section class="profile-card">
            <header class="profile-header">
                <h2>Ganti Password</h2>
                <p>Gunakan password yang kuat agar akun tetap aman.</p>
            </header>

            <form method="post" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="form-group">
                    <label>Password Saat Ini</label>
                    <div style="position: relative;">
                        <input id="current_pw" name="current_password" type="password" class="form-control" required>
                        <span onclick="toggleView('current_pw', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁️</span>
                    </div>
                    @if($errors->updatePassword->has('current_password'))
                        <small style="color: red;">{{ $errors->updatePassword->first('current_password') }}</small>
                    @endif
                </div>

                <div class="form-group">
                    <label>Password Baru</label>
                    <div style="position: relative;">
                        <input id="new_pw" name="password" type="password" class="form-control" required>
                        <span onclick="toggleView('new_pw', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁️</span>
                    </div>
                    @if($errors->updatePassword->has('password'))
                        <small style="color: red;">{{ $errors->updatePassword->first('password') }}</small>
                    @endif
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input name="password_confirmation" type="password" class="form-control" required>
                </div>

                <button type="submit" class="btn-save">Update Password</button>
                @if (session('status') === 'password-updated')
                    <p class="status-msg">✅ Password berhasil diganti!</p>
                @endif
            </form>
        </section>

        <section class="profile-card" style="border-top: 5px solid #dc3545;">
            <header class="profile-header">
                <h2 style="color: #dc3545;">Hapus Akun</h2>
                <p>Setelah akun dihapus, semua data Anda akan hilang permanen.</p>
            </header>

            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                
                <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Masukkan password Anda untuk konfirmasi penghapusan:</p>
                <div class="form-group">
                    <input name="password" type="password" class="form-control" placeholder="Password Konfirmasi" required>
                </div>

                <button type="submit" class="btn-save" style="background-color: #dc3545;">Hapus Akun Secara Permanen</button>
            </form>
        </section>

        <a href="{{ route('dashboard') }}" class="back-link">&larr; Kembali ke Dashboard</a>
    </div>

    <script>
        function toggleView(id, el) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                el.innerText = "🔒";
            } else {
                input.type = "password";
                el.innerText = "👁️";
            }
        }
    </script>
</x-app-layout>