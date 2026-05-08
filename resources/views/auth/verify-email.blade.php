<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Email - SPV Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/spv-space.css') }}">
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; background: #f8fafc;">

    <div class="card-premium" style="max-width: 500px; text-align: center; padding: 40px;">
        <h2 style="font-size: 24px; font-weight: bold; margin-bottom: 10px;">Verifikasi Email Anda ✉️</h2>
        <p style="color: #64748b; margin-bottom: 20px;">
            Terima kasih sudah mendaftar! Kami telah mengirimkan link verifikasi ke email Anda. 
            Silakan klik link tersebut untuk mulai menggunakan sistem.
        </p>

        {{-- Menampilkan pesan sukses kalau email dikirim ulang --}}
        @if (session('success'))
            <div style="background: #dcfce7; color: #166534; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-premium" style="width: 100%;">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top: 15px;">
            @csrf
            <button type="submit" style="background: none; border: none; color: #ef4444; text-decoration: underline; cursor: pointer;">
                Logout
            </button>
        </form>
    </div>

</body>
</html>