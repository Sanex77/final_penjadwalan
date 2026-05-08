<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ormawa - Lab System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body style="background-color: var(--light-bg);">

    <header style="background: white; border-bottom: 1px solid var(--border-color); padding: 15px 0; margin-bottom: 30px;">
        <div style="max-width: 1000px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px;">
            <div style="font-weight: 700; color: var(--primary-blue); font-size: 18px;">
                🚀 LabSystem <span style="font-weight: 400; color: var(--text-muted);">| Ormawa Portal</span>
            </div>
            
            <div style="display: flex; gap: 20px; align-items: center;">
                <span style="font-size: 13px; color: var(--text-main);">Halo, <strong>{{ Auth::user()->name }}</strong></span>
                <div class="dropdown-menu">
                    <a href="{{ route('profile.edit') }}">👤 Profile</a>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">🚪 Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
        <div class="auth-card" style="max-width: 100%; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
            <div class="auth-header" style="text-align: left; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
                <h2 style="font-size: 20px;">Formulir Peminjaman Lab</h2>
                <p>Silakan lengkapi detail waktu dan keperluan penggunaan laboratorium.</p>
            </div>

            @if(session('success'))
                <div style="padding: 12px; background-color: #ecfdf5; border: 1px solid #10b981; color: #065f46; border-radius: 8px; margin: 20px 0; font-size: 14px;">
                    ✔ {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 12px; border-radius: 8px; margin: 20px 0; font-size: 14px;">
                    <strong>Terjadi Kesalahan:</strong>
                    <ul style="margin-top: 5px; font-size: 13px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('booking.store') }}" method="POST" enctype="multipart/form-data" id="booking-form" style="margin-top: 25px;">
                @csrf

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px;">
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal Peminjaman</label>
                        <input type="date" id="tanggal" name="tanggal" required value="{{ old('tanggal') }}">
                    </div>

  @if(Auth::user()->role === 'dosen' || Auth::user()->role === 'spv')
    <div class="form-group">
        <label for="lab">Pilih Laboratorium</label>
        <select name="lab" id="lab" required>
            <option value="" disabled selected>-- Pilih LAB --</option>
            @for ($i = 1; $i <= 10; $i++)
                <option value="Lab {{ $i }}">LAB {{ sprintf('%02d', $i) }}</option>
            @endfor
        </select>
    </div>
@else
    <div class="form-group">
        <label>Laboratorium</label>
        <input type="text" value="DITENTUKAN_OLEH_SPV" disabled style="background: #e2e8f0; cursor: not-allowed;">
        <input type="hidden" name="lab" value="TBD"> 
    </div>
@endif

                    <div class="form-group">
                        <label for="jam_mulai">Jam Mulai</label>
                        <input type="text" id="jam_mulai" name="jam_mulai" class="timepicker" placeholder="--:--" required>
                    </div>

                    <div class="form-group">
                        <label for="jam_selesai">Jam Selesai</label>
                        <input type="text" id="jam_selesai" name="jam_selesai" class="timepicker" placeholder="--:--" required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="keperluan">Kapasitas</label>
                        <input type="number" id="kapasitas" name="kapasitas" required placeholder="cth 20 orang" value="{{ old('keperluan') }}">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="keperluan">Nama Kegiatan & kebutuhan</label>
                        <input type="text" id="keperluan" name="keperluan" required placeholder="Contoh: Pelatihan desain, kebutuhan : Photoshop, adobe premier dsb.." value="{{ old('keperluan') }}">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1; background: #f1f5f9; padding: 20px; border-radius: 12px; border: 2px dashed var(--border-color);">
                        <label for="file_surat" style="color: var(--primary-blue); font-weight: 600;">Unggah Surat Peminjaman (.PDF)</label>
                        <input type="file" id="file_surat" name="file_surat" accept="application/pdf" required style="border: none; padding: 10px 0;">
                        <small style="color: var(--text-muted); font-size: 11px;">Maksimal ukuran file: 2MB</small>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-primary" style="font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                        Kirim Pengajuan Booking
                    </button>
                </div>
            </form>
        <div style="margin-top: 50px; border-top: 2px solid var(--border-color); padding-top: 30px;">
    <h3 style="font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        📅 Riwayat Pengajuan Saya
    </h3>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">TANGGAL</th>
                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">LAB</th>
                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">WAKTU</th>
                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">KEPERLUAN</th>
                    <th style="padding: 12px; border-bottom: 2px solid var(--border-color); text-align: center;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myBookings as $book)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px;">
                        <div style="font-weight: 600;">{{ \Carbon\Carbon::parse($book->tanggal)->translatedFormat('d F Y') }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $book->hari }}</div>
                    </td>
                    <td style="padding: 12px;">
                        <span style="background: #e2e8f0; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                            {{ $book->lab }}
                        </span>
                    </td>
                    <td style="padding: 12px;">{{ $book->jam_mulai }} - {{ $book->jam_selesai }}</td>
                    <td style="padding: 12px;">{{ $book->keperluan }}</td>
                    <td style="padding: 12px; text-align: center;">
                        @if($book->status === 'pending')
                            <span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 11px; border: 1px solid #f59e0b;">
                                ⏳ MENUNGGU
                            </span>
                        @elseif($book->status === 'approved')
                            <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 11px; border: 1px solid #22c55e;">
                                ✅ DISETUJUI
                            </span>
                        @else
                            <span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 11px; border: 1px solid #ef4444;">
                                ❌ DITOLAK
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">
                        Belum ada riwayat pengajuan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
            <div style="text-align: center; margin-top: 40px; color: var(--text-muted); font-size: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                &copy; 2026 Lab System Management
            </div>
        </div>
    </div>

    <script>Q
        // Flatpickr setup
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15
        });

        // Loading Effect
        document.getElementById('booking-form').addEventListener('submit', function() {
            const btn = this.querySelector('button');
            btn.innerHTML = 'Memproses Pengajuan...';
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
        });
    </script>
</body>
</html>