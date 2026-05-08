<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
     <script defer src="{{ asset('js/spv-table.js') }}"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
    {{-- Panggil kerangka master tadi --}}
@extends('layouts.spv')

{{-- Isi judul halamannya --}}
@section('title', 'Dashboard Utama')

{{-- Isi area kontennya --}}
@section('content')
    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">Dashboard</h1>
    <p style="color: #64748b; margin-bottom: 30px;">Memantau indikator kinerja utama Anda</p>

    {{-- Kartu Info (Contoh dari desain kamu) --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 50px;">
        
        {{-- Bagian Kiri (Tabel atau Chart) --}}
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <h3>Jadwal Hari Ini</h3>
             <form action="{{ route('spv.dashboard') }}" method="GET" id="form-filter">
            <label>Cek Jadwal Tanggal:</label>
        
         <input type="date" 
               name="filter_date" 
               {{-- 🚀 Pakai request() dengan default hari ini --}}
               value="{{ request('filter_date', now()->toDateString()) }}" 
               onchange="this.form.submit()"
               class="input-inline">
        
        {{-- Tombol Reset kalau mau liat semua --}}
        @if(request('filter_date') && request('filter_date') != now()->toDateString())
            <a href="{{ route('spv.dashboard') }}" style="color:red; font-size:12px;">[Reset ke Hari Ini]</a>
        @endif
    </form>

                
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
        </tr>
    </thead>
    <tbody>
   @foreach($schedules as $s)
<tr data-hari="{{ $s->hari }}" data-lab="{{ $s->lab }}">
    
    {{-- Kolom Tanggal (Di sini kita taruh form utamanya biar HTML valid) --}}
    <td>
        <div class="font-bold text-xs" style="color: var(--primary-color);">
            {{ strtoupper($s->hari) }}
        </div>
    {{ date('d M Y', strtotime($s->tanggal)) }}
    </td>

    {{-- Kolom Lab --}}
    <td>
       {{ $s->lab}}
    </td>

    {{-- Kolom Jam --}}
    <td>
        <div style="display: flex; gap: 5px; align-items: center;">
           {{ $s->jam_mulai }}
            <span>-</span>
           {{ $s->jam_selesai }}
           </div>
    </td>

    {{-- Kolom Matkul --}}
    <td>
        {{ $s->matkul }}
    </td>

    {{-- Kolom Dosen --}}
    <td>{{ $s->dosen }}
    </td>

    {{-- Kolom Asisten (Dropdown Anti-Bentrok) --}}
    <td>
       {{ $s->nama_asisten }}
        
    </td>

    {{-- Tombol Aksi --}}
    
</tr>
@endforeach
    </tbody>
</table>
            </div>

        {{-- Bagian Kanan (Kartu Statistik) --}}
        <div style="display: flex; flex-direction: column; gap: 15px;">
            
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; justify-content: space-between;">
                <div>
                    <p style="color: #64748b; margin: 0;">Pengajuan Booking</p>
                    <h2 style="font-size: 30px; margin: 0;">{{ count($bookings) }}</h2>
                </div>
                <i class="far fa-calendar-check" style="font-size: 30px; color: #58829b;"></i>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; justify-content: space-between;">
                <div>
                    <p style="color: #64748b; margin: 0;">Total Matkul Hari Ini</p>
                    <h2 style="font-size: 30px; margin: 0;">{{ count($schedules)}}</h2>
                </div>
                <i class="fas fa-building fa-2x" style="font-size: 30px; color: #58829b;"></i>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; justify-content: space-between;">
                <div>
                    <p style="color: #64748b; margin: 0;">Jumlah Laboratorium</p>
                    <h2 style="font-size: 30px; margin: 0;">{{ $countLabs }}</h2>
                </div>
                <i class="fas fa-desktop" style="font-size: 30px; color: #58829b;"></i>
            </div>

            {{-- ⬇️ Kartu Asisten Bertugas sekarang AMAN di dalam keranjang ⬇️ --}}
            <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; justify-content: space-between;">
                <div>
                    <p style="color: #64748b; margin: 0;">Asisten Bertugas</p>
                    <h2 style="font-size: 30px; margin: 0;">{{ $asistenBertugas}}</h2> {{-- Ingat: Nanti variabel angkanya diganti pakai variabel asisten ya --}}
                </div>
                <i class="fas fa-users" style="font-size: 30px; color: #58829b;"></i> {{-- Icon saya ganti ke users biar pas --}}
            </div>

        </div> {{-- ✅ Penutup Flex Column (Kolom Kanan) --}}

    </div> {{-- ✅ Penutup Grid Utama --}}

    {{-- Tabel Asisten Bertugas (Sesuai Desain yang Kamu Mau) --}}
    <div style="margin-top: 30px; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <div style="background: #e0f2fe; padding: 10px; border-radius: 10px;">
                <i class="fas fa-user-shield" style="color: #0369a1; font-size: 20px;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: bold;">Status Petugas Asisten</h3>
                <p style="margin: 0; font-size: 13px; color: #64748b;">Daftar asisten yang menjaga laboratorium saat ini</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th style="border-radius: 10px 0 0 10px;">Nama Asisten</th>
                        <th>Menjaga Lab</th>
                        <th>Waktu Tugas</th>
                        <th style="border-radius: 0 10px 10px 0;">Mata Kuliah Kelolaan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asistenHariIni as $ast)
                    <tr>
                        <td style="font-weight: bold; color: #1e293b;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 30px; height: 30px; background: #58829b; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 12px;">
                                    {{ strtoupper(substr($ast->nama_asisten, 0, 1)) }}
                                </div>
                                {{ $ast->nama_asisten }}
                            </div>
                        </td>
                        <td>
                            <span class="badge-lab" style="background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd;">
                                {{ $ast->lab }}
                            </span>
                        </td>
                        <td style="font-family: 'Courier New', Courier, monospace; font-weight: bold;">
                            {{ $ast->jam_mulai }} - {{ $ast->jam_selesai }}
                        </td>
                        <td style="font-style: italic; color: #64748b;">
                            {{ $ast->matkul }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                            <i class="fas fa-user-slash" style="font-size: 40px; display: block; margin-bottom: 10px; opacity: 0.3;"></i>
                            Tidak ada asisten yang bertugas pada tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
</body>
</html>