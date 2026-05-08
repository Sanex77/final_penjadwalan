@extends('layouts.spv')

@section('title', 'Persetujuan Booking')

@section('content')
    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">Persetujuan Booking</h1>
    <p style="color: #64748b; margin-bottom: 20px;">Kelola permintaan peminjaman lab dari Ormawa dan Dosen.</p>

    {{-- KARTU STATISTIK PEMISAH --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #f59e0b;">
            <p style="color: #64748b; margin: 0; font-size: 14px;">Total Pending</p>
            <h2 style="font-size: 28px; margin: 0;">{{ count($bookings) }}</h2>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #3b82f6;">
            <p style="color: #64748b; margin: 0; font-size: 14px;">Booking Ormawa</p>
            <h2 style="font-size: 28px; margin: 0; color: #3b82f6;">{{ $totalOrmawa }}</h2>
        </div>

        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-left: 5px solid #10b981;">
            <p style="color: #64748b; margin: 0; font-size: 14px;">Booking Dosen/Staf</p>
            <h2 style="font-size: 28px; margin: 0; color: #10b981;">{{ $totalDosen }}</h2>
        </div>
    </div>

    {{-- TABEL VERSI ASLI KAMU (SUDAH RAPI) --}}
    <div class="card-premium overflow-hidden">
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Gmail Akun</th>
                        <th>Nama Akun</th>
                        <th>NO Wa</th>
                        <th>Nama Ormawa</th>
                        <th>Kapasitas</th>
                        <th>Keperluan/Matkul</th>
                        <th>Lab</th>
                        <th>Waktu</th>
                        <th style="white-space: nowrap;">Y/M/D & Hari</th>
                        <th>Dokumen</th>
                        <th style="text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                    <tr>
                        <td class="font-semibold">{{ $b->user->email }}</td>
                        <td class="font-semibold">{{ $b->user->name }}</td>
                        <td class="font-semibold">{{ $b->user->no_wa }}</td>
                        <td>
                            @if($b->user->nama_ormawa)
                                <span class="badge-admin">{{ $b->user->nama_ormawa }}</span>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Dosen/Staf</span>
                            @endif
                        </td>
                        <td class="font-semibold" style="text-align: center">{{ $b->kapasitas }}</td>
                        <td class="font-semibold">{{ $b->keperluan }}</td>
                        
                        <td>
                            @if($b->lab == 'TBD' || $b->lab == 'Belum Ditentukan')
                                <form action="{{ route('booking.updateLab', $b->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <select name="lab" onchange="this.form.submit()" class="cyber-select-mini">
                                        <option value="" disabled selected>-- ASSIGN_LAB --</option>
                                        @foreach($b->getLabStatuses() as $item)
                                            <option value="{{ $item['nm_lab'] }}" {{ $item['status'] === 'busy' ? 'disabled' : '' }}>
                                                {{ strtoupper($item['nm_lab']) }} {{ $item['status'] === 'busy' ? '🔒' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="badge-lab">{{ strtoupper($b->lab) }}</span>
                            @endif
                        </td>

                        <td class="text-sm">{{ $b->jam_mulai }} - {{ $b->jam_selesai }}</td>
                        <td style="white-space: nowrap;">{{ $b->tanggal }} <br> Hari : {{ $b->hari }}</td>
                        
                        <td>
                            @if($b->file_surat)
                                <a href="{{ asset('storage/' . $b->file_surat) }}" target="_blank" class="ghost-link" style="color: #ef4444; text-decoration: underline;">Buka PDF</a>
                            @else
                                <span style="color: #cbd5e1;">NO_FILE</span>
                            @endif
                        </td>

                        <td style="text-align:center">
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <form method="POST" action="{{ route('booking.approve', $b->id) }}">
                                    @csrf
                                    <button type="submit" class="btn-premium">Approve</button>
                                </form>

                                <form method="POST" action="{{ route('booking.reject', $b->id) }}" onsubmit="return confirm('Yakin ingin menolak?')">
                                    @csrf
                                    <button type="submit" class="btn-premium btn-reject">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="empty-state">Belum ada pengajuan peminjaman</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection