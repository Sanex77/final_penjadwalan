<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - Lab System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body style="background-color: var(--light-bg);">

    <header style="background: white; border-bottom: 1px solid var(--border-color); padding: 15px 0; margin-bottom: 30px;">
        <div style="max-width: 1000px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px;">
            <div style="font-weight: 700; color: var(--primary-blue); font-size: 18px;">
                🚀 LabSystem <span style="font-weight: 400; color: var(--text-muted);">| Portal Dosen </span>
            </div>
            
            <div style="display: flex; gap: 20px; align-items: center;">
                <span style="font-size: 13px; color: var(--text-main);">Halo, <strong>{{ Auth::user()->name }}</strong></span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="background: none; border: 1px solid #ef4444; color: #ef4444; padding: 5px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
        <div class="auth-card" style="max-width: 100%; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
            <div class="auth-header" style="text-align: left; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
                <h2 style="font-size: 20px;">Formulir Peminjaman atau KP Lab</h2>
                <p>Silakan lengkapi detail waktu dan keperluan penggunaan laboratorium.</p>
            </div>

            {{-- TARUH KODINGAN INI SEMENTARA BUAT NYARI PENYAKITNYA --}}
@if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
        ❌ <strong>TERJADI KESALAHAN SISTEM:</strong><br>
        {{ session('error') }}
    </div>
@endif
            {{-- 1. SATU FORM UTAMA UNTUK SEMUANYA --}}
<form action="{{ route('booking.store') }}" method="POST" enctype="multipart/form-data" id="booking-form">
    @csrf

    <div class="card-premium p-8 mb-10">
        <h3 class="font-bold mb-6">Pengajuan Booking Lab</h3>
        
        <div class="grid-form">
    {{-- BARIS 1 --}}
    <div class="form-group">
        <label>Pilih Tanggal</label>
        <input type="date" name="tanggal" id="check_tanggal" required value="{{ old('tanggal') }}">
    </div>
    
    <div class="form-group">
        <label>Jam Mulai</label>
        <input type="time" name="jam_mulai" class="timepicker" id="check_mulai" required value="{{ old('jam_mulai') }}">
    </div>

    {{-- INI YANG TADI KURANG: DROPDOWN METODE --}}
    <div class="form-group">
        <label>Metode Selesai</label>
        <select id="metode_selesai" class="input-inline" style="height: 42px;">
            <option value="manual">Manual (Pilih Jam)</option>
            <option value="sks">SKS (Otomatis)</option>
        </select>
    </div>

    {{-- BARIS 2 --}}
    <div class="form-group" id="group_sks" style="display: none;">
        <label>Jumlah SKS (1 SKS = 50 Menit)</label>
        <select id="input_sks" class="input-inline" style="height: 42px;">
            <option value="1">1 SKS (50 Menit)</option>
            <option value="2">2 SKS (100 Menit)</option>
            <option value="3">3 SKS (150 Menit)</option>
        </select>
    </div>

    <div class="form-group" id="group_jam_selesai">
        <label>Jam Selesai</label>
        <input type="time" name="jam_selesai" class="timepicker" id="check_selesai" required value="{{ old('jam_selesai') }}">
    </div>
            
    <div class="form-group" style="display: flex; align-items: flex-end;">
        <button type="button" id="btnCekLab" class="btn-premium" style="width: 100%; background: var(--slate-800); height: 42px;">
            🔍 Cek Lab Kosong
        </button>
    </div>

    {{-- BARIS 3 --}}
    <div class="form-group">
        <label>Lab Tersedia</label>
        <select name="lab" id="select_lab" disabled required>
            <option value="">-- Klik Cek Dulu --</option>
        </select>
    </div>

    <div class="form-group" style="grid-column: span 2;">
        <label>Mata Kuliah / Keperluan</label>
        <input type="text" name="keperluan" required placeholder="Contoh: Pemrograman Web (AC)" value="{{ old('keperluan') }}">
    </div>
</div>

        {{-- BAGIAN UPLOAD (KHUSUS NON-DOSEN) --}}
        <div class="mt-8">
            @if(Auth::user()->role !== 'dosen')
                <div class="form-group" style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 2px dashed #e2e8f0;">
                    <label style="color: var(--primary); font-weight: 600;">Unggah Surat Peminjaman (.PDF)</label>
                    <input type="file" name="file_surat" accept="application/pdf" required>
                    <p style="color: var(--slate-500); font-size: 11px; mt-1;">Maksimal ukuran file: 2MB</p>
                </div>
            @else
                <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; border: 1px solid #bbf7d0; color: #166534; font-size: 12px;">
                    ✨ <strong>Role Dosen Terdeteksi:</strong> Anda tidak diwajibkan mengunggah surat peminjaman.
                </div>
            @endif
        </div>

        {{-- TOMBOL SUBMIT FINAL --}}
        <div class="mt-8">
            <button type="submit" id="btnSubmitBooking" class="btn-premium" disabled style="opacity: 0.5; width: 100%; padding: 15px; font-size: 16px;">
                🚀 KIRIM PENGAJUAN BOOKING
            </button>
        </div>
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
                                ⏳ MENUNGGU PERSETUJUAN
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // 1. INISIALISASI ELEMENT
    const bookingForm = document.getElementById('booking-form');
    const btnCek = document.getElementById('btnCekLab');
    const selectLab = document.getElementById('select_lab');
    const btnSubmit = document.getElementById('btnSubmitBooking');
    
    const checkTanggal = document.getElementById('check_tanggal');
    const jamMulai = document.getElementById('check_mulai');
    const jamSelesai = document.getElementById('check_selesai');
    
    const metodeSelesai = document.getElementById('metode_selesai');
    const groupSks = document.getElementById('group_sks');
    const inputSks = document.getElementById('input_sks');

    /* ==========================================
       2. LOGIKA SKS & WAKTU
    ========================================== */
    function hitungSks() {
        if (metodeSelesai.value !== 'sks' || !jamMulai.value) return;

        const [hours, minutes] = jamMulai.value.split(':').map(Number);
        const sks = parseInt(inputSks.value);
        const totalMenitTambah = sks * 50;

        const date = new Date();
        date.setHours(hours);
        date.setMinutes(minutes + totalMenitTambah);

        const resHours = String(date.getHours()).padStart(2, '0');
        const resMinutes = String(date.getMinutes()).padStart(2, '0');

        jamSelesai.value = `${resHours}:${resMinutes}`;
        resetCekStatus(); // Tiap ganti waktu, status cek lab harus reset
    }

    metodeSelesai.addEventListener('change', function() {
        if (this.value === 'sks') {
            groupSks.style.display = 'block';
            jamSelesai.readOnly = true;
            hitungSks();
        } else {
            groupSks.style.display = 'none';
            jamSelesai.readOnly = false;
        }
    });

    jamMulai.addEventListener('input', hitungSks);
    inputSks.addEventListener('change', hitungSks);
    
    // Fungsi reset agar user wajib klik "Cek Lab" lagi kalau ganti waktu
    function resetCekStatus() {
        selectLab.disabled = true;
        selectLab.innerHTML = '<option value="">-- Klik Cek Dulu --</option>';
        btnSubmit.disabled = true;
        btnSubmit.style.opacity = '0.5';
        btnCek.innerText = '🔍 Cek Lab Kosong';
        btnCek.style.background = 'var(--slate-800)';
    }

    [checkTanggal, jamMulai, jamSelesai].forEach(el => {
        el.addEventListener('change', resetCekStatus);
    });

    /* ==========================================
       3. AJAX CEK LAB
    ========================================== */
    btnCek.addEventListener('click', async function() {
        const tanggal = checkTanggal.value;
        const mulai = jamMulai.value;
        const selesai = jamSelesai.value;

        if (!tanggal || !mulai || !selesai) {
            alert('⚠️ Lengkapi Tanggal dan Jam dulu, Pak/Bu!');
            return;
        }

        btnCek.innerText = '⏳ Mengecek...';
        
        try {
            const response = await fetch("{{ route('labs.check') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tanggal, jam_mulai: mulai, jam_selesai: selesai })
            });

            const data = await response.json();

            if (data.success) {
                selectLab.innerHTML = '';
                if (data.labs.length > 0) {
                    data.labs.forEach(lab => {
                        const option = document.createElement('option');
                        option.value = lab;
                        option.text = lab.toUpperCase();
                        selectLab.appendChild(option);
                    });
                    
                    selectLab.disabled = false;
                    btnSubmit.disabled = false;
                    btnSubmit.style.opacity = '1';
                    btnCek.innerText = '✅ Lab Tersedia';
                    btnCek.style.background = '#10b981'; // Warna sukses
                } else {
                    selectLab.innerHTML = '<option value="">Semua Lab Penuh!</option>';
                    btnCek.innerText = '❌ Penuh';
                    btnCek.style.background = '#ef4444'; // Warna gagal
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Gangguan koneksi ke server.');
            btnCek.innerText = '🔍 Cek Lab Kosong';
        }
    });

    /* ==========================================
       4. LOADING EFFECT & INITIALIZER
    ========================================== */
    bookingForm.addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        btn.innerHTML = '🚀 Mengirim Data...';
        btn.style.opacity = '0.7';
        btn.style.pointerEvents = 'none';
    });
     flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15
        });

    // Flatpickr (Opsional jika input type="text")
    // flatpickr(".timepicker", { ... });
});
    </script>
</body>
</html>