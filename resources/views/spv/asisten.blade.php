@extends('layouts.spv')

@section('title', 'Jadwal & Edit Asisten')

@section('content')
    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">Manajemen Jadwal Asisten</h1>
    <p style="color: #64748b; margin-bottom: 30px;">Kelola jadwal, hari, dan mata kuliah yang dipegang oleh asisten secara langsung.</p>

    {{-- KOTAK UPLOAD EXCEL & RESET SEMUA DATA --}}
    <div class="card-premium overflow-hidden mb-8" style="padding: 20px; background: #dbeafe; border: 1px solid #bfdbfe;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h3 style="font-size: 16px; font-weight: bold; margin: 0; color: #1e1e4a;">Unggah Jadwal Asisten (Excel/CSV)</h3>
            
            {{-- Tombol Sapu Bersih (Reset) --}}
            <form action="{{ route('asisten.clear') }}" method="POST" onsubmit="return confirm('⚠️ PERINGATAN KERAS!\n\nAnda yakin ingin menghapus SEMUA data jadwal asisten?\nTindakan ini tidak bisa dibatalkan!')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-premium" style="background-color: #ef4444; margin: 0; padding: 6px 15px; font-size: 13px;">
                    <i class="fas fa-trash-alt"></i> Kosongkan Semua Data
                </button>
            </form>
        </div>
        
        <form action="{{ route('spv.importAsisten') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 15px; align-items: center;">
            @csrf
            <input type="file" name="file_asisten" accept=".xlsx, .xls, .csv" required style="background: white; padding: 8px; border-radius: 5px; border: 1px solid #cbd5e1; flex-grow: 1;">
            <button type="submit" class="btn-premium" style="background-color: #1e1e4a; margin: 0;"><i class="fas fa-upload"></i> Import Data</button>
        </form>
    </div>


   
        
        

    {{-- FORM PENCARIAN FILTER GANDA (NAMA & HARI) --}}
    <div class="card-premium overflow-hidden mb-6" style="padding: 20px;">
        <form action="{{ route('spv.asisten') }}" method="GET" id="form-cari-asisten" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            
            {{-- Dropdown Nama --}}
            <div style="flex-grow: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #475569;">Pilih Nama Asisten:</label>
                <select name="nama" class="cyber-select-mini" style="width: 100%; padding: 10px;" onchange="this.form.submit()">
                    <option value="">-- Semua Asisten --</option>
                    @foreach($semuaAsisten as $asisten)
                        <option value="{{ $asisten->nama_asisten }}" {{ $namaDicari == $asisten->nama_asisten ? 'selected' : '' }}>
                            {{ $asisten->nama_asisten }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Dropdown Hari --}}
            <div style="flex-grow: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #475569;">Pilih Hari:</label>
                <select name="hari" class="cyber-select-mini" style="width: 100%; padding: 10px;" onchange="this.form.submit()">
                    <option value="">-- Semua Hari --</option>
                    <option value="Senin" {{ $hariDicari == 'Senin' ? 'selected' : '' }}>Senin</option>
                    <option value="Selasa" {{ $hariDicari == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                    <option value="Rabu" {{ $hariDicari == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                    <option value="Kamis" {{ $hariDicari == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                    <option value="Jumat" {{ $hariDicari == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                    <option value="Sabtu" {{ $hariDicari == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                </select>
            </div>
            
            {{-- Tombol Reset --}}
            @if($namaDicari || $hariDicari)
                <a href="{{ route('spv.asisten') }}" class="btn-premium" style="background-color: #ef4444; color: white; text-decoration: none; display: flex; align-items: center; padding: 10px 15px; margin: 0; height: 42px;">
                    <i class="fas fa-times" style="margin-right: 5px;"></i> Reset
                </a>
            @endif
        </form>
    </div>
    

    {{-- TABEL INLINE EDIT (Hanya muncul jika sudah ada filter yang aktif) --}}
    @if($namaDicari || $hariDicari)
        <div class="card-premium overflow-hidden">
            <div class="p-6 border-b flex-between">
                <h3 class="font-bold" style="color: #1e1e4a;">
                    Jadwal: <span style="color: #0ea5e9;">{{ $namaDicari ?? 'Semua Asisten' }}</span> 
                    | Hari: <span style="color: #f59e0b;">{{ $hariDicari ?? 'Semua Hari' }}</span>
                </h3>
                <div class="table-controls">
                    <span style="font-size: 13px; color: #64748b;">*Data otomatis tersimpan saat Anda mengubah isian form.</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-premium" id="asistenTable">
                    <thead>
                        <tr>
                            <th>Nama Asisten</th>
                            <th>Hari</th>
                            <th>Jam (Mulai - Selesai)</th>
                            <th>Mata Kuliah Kelolaan</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asistenSchedules as $a)
                        <tr>
                            <form action="{{ route('asisten.update', $a->id) }}" method="POST" id="update-asisten-{{ $a->id }}">
                                @csrf
                                @method('PATCH')
                            </form>

                            {{-- Kolom Nama Asisten --}}
                            <td>
                                @if($namaDicari)
                                    {{-- Jika filter nama aktif, KUNCI kolom ini biar gak bisa diedit --}}
                                    <input type="hidden" name="nama_asisten" value="{{ $a->nama_asisten }}" form="update-asisten-{{ $a->id }}">
                                    <div class="font-bold" style="color: var(--primary-color); padding: 8px 10px; cursor: not-allowed; opacity: 0.8;" title="Terkunci oleh filter pencarian">
                                        <i class="fas fa-lock" style="font-size: 10px; color: #94a3b8;"></i> {{ $a->nama_asisten }}
                                    </div>
                                @else
                                    {{-- Jika cuma filter Hari (Nama gak difilter), tetap tampilkan input biasa --}}
                                    <input type="text" name="nama_asisten" value="{{ $a->nama_asisten }}" class="input-inline font-bold" 
                                           style="color: var(--primary-color);"
                                           form="update-asisten-{{ $a->id }}" 
                                           onchange="document.getElementById('update-asisten-{{ $a->id }}').submit()">
                                @endif
                            </td>

                            <td>
                                <select name="hari" class="cyber-select-mini" form="update-asisten-{{ $a->id }}" onchange="document.getElementById('update-asisten-{{ $a->id }}').submit()">
                                    <option value="Senin" {{ strtolower($a->hari) == 'senin' ? 'selected' : '' }}>Senin</option>
                                    <option value="Selasa" {{ strtolower($a->hari) == 'selasa' ? 'selected' : '' }}>Selasa</option>
                                    <option value="Rabu" {{ strtolower($a->hari) == 'rabu' ? 'selected' : '' }}>Rabu</option>
                                    <option value="Kamis" {{ strtolower($a->hari) == 'kamis' ? 'selected' : '' }}>Kamis</option>
                                    <option value="Jumat" {{ strtolower($a->hari) == 'jumat' ? 'selected' : '' }}>Jumat</option>
                                    <option value="Sabtu" {{ strtolower($a->hari) == 'sabtu' ? 'selected' : '' }}>Sabtu</option>
                                </select>
                            </td>

                            <td>
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    <input type="time" name="jam_mulai" value="{{ \Carbon\Carbon::parse($a->jam_mulai)->format('H:i') }}" class="input-inline" 
                                           form="update-asisten-{{ $a->id }}"
                                           onchange="document.getElementById('update-asisten-{{ $a->id }}').submit()">
                                    <span>-</span>
                                    <input type="time" name="jam_selesai" value="{{ \Carbon\Carbon::parse($a->jam_selesai)->format('H:i') }}" class="input-inline" 
                                           form="update-asisten-{{ $a->id }}"
                                           onchange="document.getElementById('update-asisten-{{ $a->id }}').submit()">
                                </div>
                            </td>

                            <td>
                                <input type="text" name="mata_kuliah" value="{{ $a->mata_kuliah }}" class="input-inline font-semibold" 
                                       style="width: 100%; min-width: 250px;"
                                       form="update-asisten-{{ $a->id }}"
                                       onchange="document.getElementById('update-asisten-{{ $a->id }}').submit()">
                            </td>

                            <td style="text-align:right">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <form method="POST" action="{{ route('asisten.destroy', $a->id) }}" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete-mini">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state" style="text-align: center; padding: 30px;">
                                <i class="fas fa-calendar-times" style="font-size: 30px; color: #cbd5e1; margin-bottom: 10px;"></i><br>
                                Tidak ada jadwal yang cocok dengan filter.
                            </td>
                        </tr>
                        @endforelse
                        {{-- ============================================== --}}
                        {{-- BARIS KHUSUS UNTUK TAMBAH JADWAL BARU          --}}
                        {{-- ============================================== --}}
                        <tr style="background-color: #f8fafc; border-top: 3px solid #cbd5e1;">
                            <form action="{{ route('asisten.store') }}" method="POST">
                                @csrf
                                
                                {{-- Kolom Nama (Otomatis terisi jika difilter) --}}
                                <td>
                                    @if($namaDicari)
                                        <input type="hidden" name="nama_asisten" value="{{ $namaDicari }}">
                                        <div class="font-bold" style="color: var(--primary-color); padding: 5px 10px;">
                                            <i class="fas fa-lock" style="font-size: 10px; color: #94a3b8;"></i> {{ $namaDicari }}
                                        </div>
                                    @else
                                        <select name="nama_asisten" class="cyber-select-mini" style="border-color: #10b981;" required>
                                            <option value="">-- Ketik/Pilih Asisten --</option>
                                            @foreach($semuaAsisten as $asisten)
                                                <option value="{{ $asisten->nama_asisten }}">{{ $asisten->nama_asisten }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>

                                {{-- Kolom Hari (Otomatis terisi jika difilter) --}}
                                <td>
                                    <select name="hari" class="cyber-select-mini" style="border-color: #10b981;" required>
                                        <option value="Senin" {{ $hariDicari == 'Senin' ? 'selected' : '' }}>Senin</option>
                                        <option value="Selasa" {{ $hariDicari == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                                        <option value="Rabu" {{ $hariDicari == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                                        <option value="Kamis" {{ $hariDicari == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                                        <option value="Jumat" {{ $hariDicari == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                                        <option value="Sabtu" {{ $hariDicari == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                                    </select>
                                </td>

                                {{-- Kolom Jam --}}
                                <td>
                                    <div style="display: flex; gap: 5px; align-items: center;">
                                        <input type="time" name="jam_mulai" class="input-inline" style="border-color: #10b981;" required>
                                        <span>-</span>
                                        <td style="padding: 12px;">
                                    <div style="display: flex; gap: 5px; align-items: center;">
                                
                                        <span style="font-weight: bold; color: #10b981; font-size: 12px;">+</span>
                                        
                                        <select name="sks" class="cyber-select-mini" style="width: 80px; border-color: #10b981;" required>
                                            <option value="1">1 SKS</option>
                                            <option value="2">2 SKS</option>
                                            <option value="3">3 SKS</option>
                                        </select>
                                    </div>
                                </td>
                                

                                {{-- Kolom Matkul --}}
                                <td>
                                    <input type="text" name="mata_kuliah" class="input-inline font-semibold" placeholder="Ketik matkul baru..." 
                                           style="width: 100%; min-width: 250px; border-color: #10b981;" required>
                                </td>

                                {{-- Tombol Aksi Tambah --}}
                                <td style="text-align:right">
                                    <button type="submit" class="btn-premium" style="background-color: #10b981; color: white; margin: 0; padding: 8px 15px; font-size: 13px;">
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </td>
                            </form>
                        </tr>
                        {{-- ============================================== --}}
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- State kosong saat belum milih filter --}}
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 10px; border: 1px dashed #cbd5e1;">
            <i class="fas fa-filter" style="font-size: 40px; color: #94a3b8; margin-bottom: 15px;"></i>
            <h3 style="color: #475569; font-weight: bold;">Gunakan Filter Terlebih Dahulu</h3>
            <p style="color: #94a3b8; font-size: 14px;">Pilih Nama Asisten atau Hari di atas untuk mulai melihat dan mengedit jadwal.</p>
        </div>
    @endif
@endsection