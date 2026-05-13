<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="{{ asset('css/spv-space.css') }}">
      <script defer src="{{ asset('js/spv-table.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Manajemen Jadwal</title>
</head>
<body>
@extends('layouts.spv')
@section('title', 'Manajemen Jadwal')

@section('content')
    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">Manajemen Jadwal</h1>
    <p style="color: #64748b; margin-bottom: 30px;">Kelola jadwal praktikum dan persetujuan peminjaman lab.</p>
    <div class="card-premium overflow-hidden">
                <div class="p-6 border-b flex-between">
                    <h3 class="font-bold">Daftar Jadwal Lab</h3>
                    <div class="filters">
                        <button id="btnCetakPDF" class="btn-premium" style="background-color: #ef4444;">
                        PDF 📄
                        </button>
                        <button onclick="toggleTambahJadwal()" class="btn-premium" style="background-color: #3b82f6; color: white;">
        ➕ Tambah Jadwal Manual
    </button>
                        <select id="filterDay" class="filter-select">
                            <option value="">🌍 All Days</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                        <select id="filterLab" class="filter-select">
                            <option value="">🧪 All Labs</option>
                            @for($i=1; $i<=11; $i++)
                                @php $formatLab = 'LAB ' . sprintf('%02d', $i); @endphp
                                <option value="{{ $formatLab }}">{{ $formatLab }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                

<div id="form-tambah-jadwal" class="card-premium" style="display: none; margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 8px;">
    <div class="p-4 border-b" style="background-color: #f8fafc; border-radius: 8px 8px 0 0;">
        <h3 class="font-bold" style="font-size: 16px;">Form Tambah Jadwal Manual</h3>
    </div>
    <div class="p-6">
        <form action="{{ route('schedule.store') }}" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            @csrf
            <div>
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Tanggal</label>
                <input type="date" name="tanggal" required class="input-inline" style="width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Ruang Lab</label>
                <select name="lab" required class="filter-select" style="width: 100%;">
                    <option value="">-- Pilih Lab --</option>
                    @foreach($labs as $lab)
                        <option value="{{ $lab->nm_lab }}">{{ $lab->nm_lab }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Jam Mulai</label>
                <input type="time" name="jam_mulai" required class="input-inline" style="width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Jumlah SKS (Untuk hitung otomatis jam selesai)</label>
                <input type="number" name="sks" required min="1" max="6" class="input-inline" style="width: 100%;" placeholder="Contoh: 2">
            </div>
            <div>
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Mata Kuliah</label>
                <input type="text" name="matkul" required class="input-inline" style="width: 100%;" placeholder="Nama Matkul">
            </div>
            <div>
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Nama Dosen</label>
                <input type="text" name="dosen" required class="input-inline" style="width: 100%;" placeholder="Nama Dosen">
            </div>
            <div style="grid-column: span 2; text-align: right; margin-top: 10px;">
                <button type="submit" class="btn-premium" style="background-color: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                    💾 Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
    <div class="mb-4">
    <form action="{{ route('spv.jadwal') }}" method="GET" id="form-filter">
        <label>Cek Jadwal Tanggal:</label>
        
        <input type="date" 
               name="filter_date" 
               value="{{ request('filter_date', now()->toDateString()) }}" 
               onchange="this.form.submit()"
               class="input-inline">
        
        {{-- Tombol Reset - Arahkan balik ke halaman Manajemen Jadwal tanpa parameter --}}
        @if(request('filter_date'))
            <a href="{{ route('spv.jadwal') }}" style="color:red; font-size:12px;">[Reset Filter]</a>
        @endif
    </form>
</div>
                
                <div class="table-controls">
                    <span>Show</span>
                    <select class="limitSelect">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>data</span>
                </div>

                <table class="table-premium" id="scheduleTable">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Lab</th>
            <th>Jam (Mulai - Selesai)</th>
            <th>Mata Kuliah</th>
            <th>Dosen</th>
            <th>Asisten</th>
            <th style="text-align:right">Aksi</th>
        </tr>
    </thead>
    <tbody>
   @foreach($schedules as $s)
<tr data-hari="{{ $s->hari }}" data-lab="{{ $s->lab }}">
    
    {{-- Kolom Tanggal (Di sini kita taruh form utamanya biar HTML valid) --}}
    <td>
        <form action="{{ route('schedule.update', $s->id) }}" method="POST" id="update-form-{{ $s->id }}">
            @csrf
            @method('PATCH')
        </form>

        <div class="font-bold text-xs" style="color: var(--primary-color);">
            {{ strtoupper($s->hari) }}
        </div>
        <input type="date" name="tanggal" value="{{ \Carbon\Carbon::parse($s->tanggal)->format('Y-m-d') }}" class="input-inline" 
               form="update-form-{{ $s->id }}" 
               onchange="document.getElementById('update-form-{{ $s->id }}').submit()">
    </td>

    {{-- Kolom Lab --}}
    {{-- Kolom Lab (Dengan Satpam Anti-Bentrok) --}}
    <td>
        <select name="lab" class="cyber-select-mini" 
                form="update-form-{{ $s->id }}" 
                onchange="document.getElementById('update-form-{{ $s->id }}').submit()">
            
            {{-- Panggil fungsi dari Model yang baru kita buat --}}
            @foreach($s->getLabStatuses() as $lab)
                @if($lab['status'] === 'busy')
                    {{-- Kalau Lab Sibuk: Kunci dan kasih warna merah --}}
                    <option value="" disabled style="color: #ef4444; background: #fee2e2;">
                        🔒 {{ $lab['nm_lab'] }} (Dipakai)
                    </option>
                @else
                    {{-- Kalau Lab Kosong: Bisa dipilih --}}
                    <option value="{{ $lab['nm_lab'] }}" {{ $s->lab == $lab['nm_lab'] ? 'selected' : '' }}>
                        {{ $lab['nm_lab'] }}
                    </option>
                @endif
            @endforeach

        </select>
    </td>

    {{-- Kolom Jam --}}
    <td>
        <div style="display: flex; gap: 5px; align-items: center;">
            <input type="time" name="jam_mulai" value="{{ $s->jam_mulai }}" class="input-inline" form="update-form-{{ $s->id }}">
            <span>-</span>
            <input type="time" name="jam_selesai" value="{{ $s->jam_selesai }}" class="input-inline" form="update-form-{{ $s->id }}">
        </div>
    </td>

    {{-- Kolom Matkul --}}
    <td>
        <input type="text" name="matkul" value="{{ $s->matkul }}" class="input-inline font-semibold" form="update-form-{{ $s->id }}">
    </td>

    {{-- Kolom Dosen --}}
    <td>
        <input type="text" name="dosen" value="{{ $s->dosen }}" class="input-inline text-xs" form="update-form-{{ $s->id }}">
    </td>

    {{-- Kolom Asisten (Dropdown Anti-Bentrok) --}}
    <td>
        <select name="nama_asisten" class="cyber-select-mini" style="width: 100%; min-width: 120px;" 
                form="update-form-{{ $s->id }}" 
                onchange="document.getElementById('update-form-{{ $s->id }}').submit()">
            <option value="">-- Pilih Asisten --</option>
            
           @foreach($s->getAssistantStatuses() as $asisten)
                @if($asisten->is_busy)
                    @if($s->nama_asisten == $asisten->nama)
                        {{-- Jika asisten ini sudah terlanjur dipilih sebelumnya, tapi ternyata sekarang jadwalnya bentrok --}}
                        <option value="{{ $asisten->nama }}" selected style="color: #ef4444; font-weight: bold;">
                            ⚠️ {{ $asisten->nama }} {{ $asisten->label }}
                        </option>
                    @else
                        {{-- Asisten sibuk, disable opsi ini dan tampilkan alasannya (Matkul / Lab) --}}
                        <option value="" disabled style="color: #ef4444; background: #fee2e2;">
                            🔒 {{ $asisten->nama }} {{ $asisten->label }}
                        </option>
                    @endif
                @else
                    {{-- Asisten tersedia --}}
                    <option value="{{ $asisten->nama }}" {{ $s->nama_asisten == $asisten->nama ? 'selected' : '' }}>
                        {{ $asisten->nama }}
                    </option>
                @endif
            @endforeach
        </select>
    </td>

    {{-- Tombol Aksi --}}
    <td style="text-align:right">
        <div style="display: flex; gap: 8px; justify-content: flex-end;">
            {{-- Tombol Simpan (Panggil form berdasarkan ID) --}}
            <button type="submit" class="btn-save-icon" title="Simpan Perubahan" form="update-form-{{ $s->id }}">💾</button>

            {{-- Tombol Hapus (Separate Form) --}}
            <form method="POST" action="{{ route('schedule.destroy', $s->id) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                @csrf 
                @method('DELETE')
                <button type="submit" class="btn-delete-mini">Hapus</button>
            </form>
        </div>
    </td>
</tr>
@endforeach
    </tbody>
</table>

                <div id="noDataMessage" class="no-data">🚫 Tidak ada jadwal yang cocok.</div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
<Script>
    function toggleLabManager() {
    const content = document.getElementById('lab-manager-content');
    const icon = document.getElementById('toggle-icon');
    
    if (content.style.display === "none") {
        content.style.display = "block";
        icon.style.transform = "rotate(90deg)"; // Efek burger berputar
    } else {
        content.style.display = "none";
        icon.style.transform = "rotate(0deg)";
    }
    
}
flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15
        });
        // Cek apakah browser mendukung Service Worker dan Push Notif
    // ... di dalam script yang kemarin ...
if (permission === 'granted') {
    console.log('🔔 SPV ngasih izin!');
    
    // Ambil alamat unik browser (Subscription)
    swReg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array('{{ env("VAPID_PUBLIC_KEY") }}')
    }).then(function(subscription) {
        // Kirim alamat ini ke Laravel lewat Fetch API
        fetch('/subscribe', {
            method: 'POST',
            body: JSON.stringify(subscription),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => console.log('✅ Alamat browser SPV udah disimpen ke database!'));
    });
}

// Fungsi pembantu buat convert kunci VAPID (Taruh di paling bawah script)
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) { outputArray[i] = rawData.charCodeAt(i); }
    return outputArray;
}
// Contoh kalau pakai tombol
const btnNotif = document.getElementById('btnAktifkanNotif');

if (btnNotif) {
    btnNotif.addEventListener('click', () => {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                // ... jalankan proses subscribe ngrok/laravel di sini ...
                alert('Notifikasi berhasil diaktifkan!');
            } else {
                alert('Yah, notifikasi diblokir. Buka gembok di URL buat buka blokirannya.');
            }
        });
    });
}

function toggleAsistenManager() {
        const content = document.getElementById('asisten-manager-content');
        const icon = document.getElementById('toggle-icon-asisten');
        
        if (content.style.display === "none") {
            content.style.display = "block";
            icon.style.transform = "rotate(90deg)"; // Efek burger berputar
        } else {
            content.style.display = "none";
            icon.style.transform = "rotate(0deg)"; // Kembali semula
        }
    }

    function toggleTambahJadwal() {
    const form = document.getElementById('form-tambah-jadwal');
    if (form.style.display === "none") {
        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
}
</Script>
@endsection
</body>
</html>

