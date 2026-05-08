<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penjadwalan Lab ICT</title>
    
    {{-- Menggunakan Tailwind CSS untuk styling cepat dan modern --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Script untuk cetak PDF --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
<script defer src="{{ asset('js/spv-table.js') }}"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            /* Background gradient cerah sesuai gambar referensi */
            background: linear-gradient(135deg, #e6f0fa 0%, #9cbdf0 100%);
            min-height: 100vh;
            color: #1f2937;
        }

        /* Warna Header Tabel sesuai referensi gambar */
        .table-header-custom {
            background-color: #799cbd;
            color: #111827;
        }

        /* Styling Custom Scrollbar untuk tabel */
        .table-wrapper::-webkit-scrollbar { height: 8px; }
        .table-wrapper::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .table-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .table-wrapper::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="flex flex-col">

    {{-- NAVBAR --}}
    <nav class="flex justify-between items-center py-6 px-8 md:px-16">
        <div class="text-xl font-extrabold text-[#1e3a8a] tracking-tight">
            Penjadwalan Lab ICT
        </div>
        
        <div class="hidden md:flex space-x-10 text-sm font-semibold text-gray-700">
            <a href="#" class="hover:text-blue-700 transition">Home</a>
            <a href="#" class="hover:text-blue-700 transition">About</a>
            <a href="#" class="hover:text-blue-700 transition">Contact</a>
            <a href="#" class="text-blue-700 border-b-2 border-blue-700 pb-1">Jadwal</a>
        </div>

        <div>
            @auth
                <a href="{{ route('spv.dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-md font-medium shadow-md transition">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="bg-[#3b82f6] hover:bg-blue-700 text-white px-7 py-2.5 rounded-md font-semibold shadow-md transition">Masuk</a>
            @endauth
        </div>
    </nav>

    {{-- HERO SECTION --}}
    <header class="text-center mt-12 mb-14 px-4">
        <h1 class="text-4xl md:text-5xl font-bold text-[#1e293b] mb-4 tracking-tight">Selamat Datang di Penjadwalan Lab ICT</h1>
        <p class="text-gray-600 max-w-3xl mx-auto text-base md:text-lg">
            Kami siap membantu kelancaran agenda Anda melalui sistem reservasi yang terintegrasi. Silakan cek ketersediaan ruang dan mulai jadwal Anda di sini.
        </p>
    </header>

    {{-- FILTER & KONTROL SECTION --}}
    <main class="max-w-7xl mx-auto px-4 w-full flex-grow flex flex-col items-center">
        
        <div class="w-full flex flex-col md:flex-row justify-between items-end mb-4 gap-4">
            
            {{-- Bagian Kiri: Filters --}}
            <div class="flex flex-wrap items-center gap-3 bg-white/50 p-2 rounded-lg backdrop-blur-sm shadow-sm border border-white/40">
                <div class="flex items-center gap-2 bg-white px-3 py-2 rounded border border-gray-200">
                    <i class="fas fa-search text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Cari Matkul/Dosen..." class="outline-none text-sm w-40 bg-transparent">
                </div>

                <input type="date" id="filterDate" value="{{ $filterDate ?? date('Y-m-d') }}" class="px-3 py-2 rounded text-sm font-medium border border-gray-200 bg-white shadow-sm outline-none focus:ring-2 focus:ring-blue-400">
                
                <select id="filterLab" class="px-3 py-2 rounded text-sm font-medium border border-gray-200 bg-white shadow-sm outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Ruang Lab</option>
                    @for($i=1; $i<=11; $i++)
                        @php $formatLab = 'LAB ' . sprintf('%02d', $i); @endphp
                        <option value="{{ $formatLab }}">{{ $formatLab }}</option>
                    @endfor
                </select>

                <select id="filterSession" class="px-3 py-2 rounded text-sm font-medium border border-gray-200 bg-white shadow-sm outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Semua Sesi</option>
                    <option value="pagi">Pagi (07:00 - 12:20)</option>
                    <option value="siang">Siang (12:20 - 16:05)</option>
                    <option value="sore">Sore (16:05 - 18:30)</option>
                    <option value="malam">Malam (18:30 - 22:00)</option>
                </select>
            </div>

            {{-- Bagian Kanan: Tombol Cetak --}}
            <button id="downloadPdfBtn" class="bg-[#3b82f6] hover:bg-blue-700 text-white px-6 py-2.5 rounded-md font-bold shadow-md transition flex items-center gap-2 uppercase tracking-wide text-sm">
                Cetak Jadwal
            </button>
        </div>

        {{-- TABEL JADWAL --}}
        <div class="w-full bg-white shadow-xl rounded-sm overflow-hidden mb-8 border border-gray-200">
            <div class="table-wrapper overflow-x-auto w-full">
                <table class="w-full text-center border-collapse whitespace-nowrap" id="scheduleTable">
                    <thead>
                        <tr class="table-header-custom text-sm uppercase tracking-wider">
                            <th class="py-4 px-4 border border-gray-300 font-bold">Mata Kuliah</th>
                            <th class="py-4 px-4 border border-gray-300 font-bold">Waktu</th>
                            <th class="py-4 px-4 border border-gray-300 font-bold">Ruang Lab</th>
                            <th class="py-4 px-4 border border-gray-300 font-bold">Nama Dosen</th>
                            <th class="py-4 px-4 border border-gray-300 font-bold">Asisten Lab</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="text-gray-700 text-sm">
                        @forelse($schedules ?? [] as $s)
                        <tr class="schedule-row hover:bg-blue-50 transition border-b border-gray-200" 
                            data-date="{{ \Carbon\Carbon::parse($s->tanggal)->format('Y-m-d') }}" 
                            data-lab="{{ $s->lab }}">
                            <td class="py-3 px-4 border-r border-gray-200 font-medium search-target">{{ $s->matkul }}</td>
                            <td class="py-3 px-4 border-r border-gray-200">
                                <span class="font-bold text-blue-700 time-text">{{ date('H:i', strtotime($s->jam_mulai)) }} - {{ date('H:i', strtotime($s->jam_selesai)) }}</span>
                                <div class="text-xs text-gray-500">{{ $s->hari }}, {{ date('d M Y', strtotime($s->tanggal)) }}</div>
                            </td>
                            <td class="py-3 px-4 border-r border-gray-200 font-bold">{{ $s->lab }}</td>
                            <td class="py-3 px-4 border-r border-gray-200 search-target">{{ $s->dosen }}</td>
                            <td class="py-3 px-4">{{ $s->nama_asisten ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr id="emptyState">
                            <td colspan="5" class="py-10 text-center text-gray-500">
                                <i class="fas fa-calendar-times text-4xl mb-3 text-gray-300"></i>
                                <p>Tidak ada jadwal yang sesuai dengan filter.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- STATUS BAWAH TABEL --}}
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex justify-between items-center text-sm text-gray-600">
                <span id="rowCountInfo">Menampilkan semua jadwal terpilih.</span>
            </div>
        </div>

        {{-- FLOATING GRID ICON --}}
        <div class="mb-10 bg-white p-4 rounded-2xl shadow-lg cursor-pointer hover:bg-gray-50 transition">
            <i class="fas fa-qrcode text-2xl text-gray-700"></i>
        </div>

    </main>

    {{-- LOGIKA JAVASCRIPT MASTER (HYBRID FILTER) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterDate = document.getElementById('filterDate');
            const filterLab = document.getElementById('filterLab');
            const filterSession = document.getElementById('filterSession');
            const rows = document.querySelectorAll('.schedule-row');
            const rowCountInfo = document.getElementById('rowCountInfo');
            const emptyState = document.getElementById('emptyState');

            function filterTable() {
                let count = 0;
                const searchVal = searchInput.value.toLowerCase();
                const labVal = filterLab.value;
                const sessionVal = filterSession.value;

                rows.forEach(row => {
                    const rLab = row.getAttribute('data-lab');
                    const timeText = row.querySelector('.time-text').innerText.split(' - ')[0].trim();
                    const textContent = row.innerText.toLowerCase();

                    // Cek Kondisi (Tanggal dihilangkan karena sudah difilter dari server saat reload)
                    const matchSearch = searchVal === '' || textContent.includes(searchVal);
                    const matchLab = labVal === '' || rLab === labVal;
                    
                    let matchSession = true;
                    if (sessionVal === "pagi") matchSession = (timeText >= "07:00" && timeText < "12:20");
                    else if (sessionVal === "siang") matchSession = (timeText >= "12:20" && timeText < "16:05");
                    else if (sessionVal === "sore") matchSession = (timeText >= "16:05" && timeText < "18:30");
                    else if (sessionVal === "malam") matchSession = (timeText >= "18:30" && timeText <= "22:00");

                    // Terapkan display
                    if (matchSearch && matchLab && matchSession) {
                        row.style.display = '';
                        count++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                rowCountInfo.innerText = `Menampilkan ${count} jadwal.`;
                
                // Tampilkan pesan kosong jika tidak ada data
                if(emptyState) {
                    emptyState.style.display = count === 0 ? '' : 'none';
                } else if(count === 0 && rows.length > 0) {
                    const tbody = document.getElementById('tableBody');
                    let dyn = document.getElementById('dynamicEmpty');
                    if(!dyn) {
                        const tr = document.createElement('tr');
                        tr.id = 'dynamicEmpty';
                        tr.innerHTML = `<td colspan="5" class="py-8 text-center text-gray-500">Tidak ada jadwal ditemukan untuk filter ini.</td>`;
                        tbody.appendChild(tr);
                    }
                } else {
                    const dyn = document.getElementById('dynamicEmpty');
                    if(dyn) dyn.remove();
                }
            }

            // 🚀 1. FILTER INSTAN (Tanpa Reload)
            searchInput.addEventListener('input', filterTable);
            filterLab.addEventListener('change', filterTable);
            filterSession.addEventListener('change', filterTable);

            // 🚀 2. KHUSUS TANGGAL (Picu Reload ke Server biar Enteng)
            filterDate.addEventListener('change', function() {
                const tbody = document.getElementById('tableBody');
                // Efek loading animasi saat ganti tanggal
                tbody.innerHTML = '<tr><td colspan="5" class="py-10 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-3"></i><p>Memuat data tanggal ' + this.value + '...</p></td></tr>';
                // Arahkan URL (Reload) dengan parameter tanggal
                window.location.href = window.location.pathname + '?filter_date=' + this.value;
            });

            // Eksekusi filter pertama kali halaman dimuat
            filterTable();

            // 🚀 3. LOGIKA CETAK PDF
            document.getElementById('downloadPdfBtn').addEventListener('click', function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('landscape');
                
                doc.setFontSize(18);
                doc.text('Jadwal Laboratorium ICT Budi Luhur', 14, 20);
                
                doc.setFontSize(11);
                doc.text(`Tanggal Filter: ${filterDate.value || 'Semua'} | Ruang: ${filterLab.value || 'Semua'}`, 14, 28);

                // Plugin autotable ini otomatis mengabaikan baris HTML yang style="display: none"
                doc.autoTable({
                    html: '#scheduleTable',
                    startY: 35,
                    theme: 'grid',
                    headStyles: { fillColor: [121, 156, 189] },
                    styles: { fontSize: 10, halign: 'center' }
                });

                doc.save(`Jadwal_Lab_${filterDate.value || 'All'}.pdf`);
            });
        });
    </script>
</body>
</html>