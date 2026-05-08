<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Lab Budi Luhur</title>
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="profile-page">
        <div class="profile-card">
            
            <header class="profile-header">
                <h2>Informasi Profil</h2>
                <p>Kelola data diri dan nomor WhatsApp Anda</p>
            </header>

            {{-- Form tersembunyi untuk verifikasi email (bawaan Breeze) --}}
            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" 
                           value="{{ old('name', $user->name) }}" required autofocus>
                    @if($errors->has('name'))
                        <small style="color: red;">{{ $errors->first('name') }}</small>
                    @endif
                </div>

                <div class="form-group">
                    <label>Email Institusi</label>
                    <input type="email" class="form-control form-control-readonly" 
                           value="{{ $user->email }}" readonly>
                    <small style="font-size: 10px; color: #aaa;">*Email tidak dapat diubah demi keamanan.</small>
                </div>

                <div class="form-group">
                    <label>Nomor WhatsApp</label>
                    <input type="text" name="no_wa" class="form-control" 
                           placeholder="628xxx" value="{{ old('no_wa', $user->no_wa) }}" required>
                    @if($errors->has('no_wa'))
                        <small style="color: red;">🚨 {{ $errors->first('no_wa') }}</small>
                    @endif
                </div>

                <button type="submit" class="btn-save">Simpan Perubahan</button>

                @if (session('status') === 'profile-updated')
                    <div class="status-msg">
                        ✅ Profil berhasil diperbarui!
                    </div>
                @endif
            </form>

            <a href="{{ route('dashboard') }}" class="back-link">&larr; Kembali ke Dashboard</a>
        </div>
    </div>

</body>
</html>