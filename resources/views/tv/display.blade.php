<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>TV DISPLAY - LAB COMPUTER</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --dark: #1e293b;
            --light: #f8fafc;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: var(--dark);
            height: 100vh;
            overflow: hidden; /* TV gak boleh ada scrollbar */
        }

        /* HEADER TV */
        .tv-header {
            background: var(--white);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid var(--primary);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .logo-section { display: flex; align-items: center; gap: 20px; }
        .logo-circle {
            width: 60px;
            height: 60px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: white;
        }

        .title-text h1 { font-size: 28px; font-weight: 800; color: var(--primary); text-transform: uppercase; }
        .title-text p { font-size: 16px; color: var(--slate-500); }

        .clock-section { text-align: right; }
        #live-clock { font-size: 32px; font-weight: 800; color: var(--dark); }
        #live-date { font-size: 16px; font-weight: 600; color: var(--primary); }

        /* TABLE AREA */
        .main-display { padding: 30px 40px; }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 15px; /* Jarak antar baris */
        }

        th {
            text-align: left;
            padding: 10px 25px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--slate-500);
        }

        tr {
            background: var(--white);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: 0.3s;
        }

        td {
            padding: 25px;
            font-size: 20px;
            font-weight: 600;
        }

        td:first-child { border-radius: 12px 0 0 12px; border-left: 8px solid var(--primary); }
        td:last-child { border-radius: 0 12px 12px 0; text-align: right; }

        .lab-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 800;
        }

        .time-tag { color: var(--primary); font-weight: 800; }
        .lecturer { color: #64748b; font-size: 16px; }

        /* FOOTER INFO */
        .footer-info {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: var(--dark);
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            font-weight: 600;
        }

        /* HIDE FILTERS (Untuk JS Saja) */
        #hidden-controls { display: none; }

        .status-pill {
            background: #22c55e;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 12px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>

    <header class="tv-header">
        <div class="logo-section">
            <div class="logo-circle">🚀</div>
            <div class="title-text">
                <h1>Jadwal Laboratorium Komputer</h1>
                <p>Universitas Budi Luhur • Real-time Display</p>
            </div>
        </div>
        <div class="clock-section">
            <div id="live-clock">00:00:00</div>
            <div id="live-date">Memuat Tanggal...</div>
        </div>
    </header>

    <div id="hidden-controls">
        <select id="filterDay"><option value=""></option><option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option><option>Sabtu</option></select>
        <select id="filterLab"><option value=""></option></select>
        <select id="filterSession">
            <option value="pagi">pagi</option>
            <option value="siang">siang</option>
            <option value="sore">sore</option>
            <option value="malam">malam</option>
        </select>
        <select id="limitSelect"><option value="6">6</option></select> </div>

    <main class="main-display">
        <table>
            <thead>
                <tr>
                    <th>RUANGAN</th>
                    <th>MATA KULIAH</th>
                    <th>DOSEN PENGAMPU</th>
                    <th>WAKTU</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @foreach($schedules as $s)
                <tr data-day="{{ $s->hari }}" data-lab="{{ $s->lab }}">
                    <td><span class="lab-badge">{{ $s->lab }}</span></td>
                    <td>{{ $s->matkul }}</td>
                    <td class="lecturer">👤 {{ $s->dosen }}</td>
                    <td><span class="time-tag time">{{ date('H:i', strtotime($s->jam_mulai)) }} - {{ date('H:i', strtotime($s->jam_selesai)) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>

    <footer class="footer-info">
        <div>Sesi Aktif: <span id="current-session-name">-</span></div>
        <div style="display: flex; gap: 20px; align-items: center;">
            <span class="status-pill">LIVE MONITORING</span>
            <span id="page-indicator">Halaman 1/1</span>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const daySelect = document.getElementById("filterDay");
            const sessionSelect = document.getElementById("filterSession");
            const limitSelect = document.getElementById("limitSelect");
            const tbody = document.getElementById("tableBody");
            const rows = Array.from(tbody.querySelectorAll("tr"));
            const pageIndicator = document.getElementById("page-indicator");

            let currentPage = 1;

            // 1. JAM LIVE
            function updateClock() {
                const now = new Date();
                document.getElementById('live-clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false });
                document.getElementById('live-date').innerText = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            }
            setInterval(updateClock, 1000);

            // 2. DETEKSI SESI
            function detectCurrentSession() {
                const now = new Date();
                const time = now.getHours().toString().padStart(2, '0') + ":" + now.getMinutes().toString().padStart(2, '0');
                if (time >= "07:00" && time < "12:20") return "pagi";
                if (time >= "12:20" && time < "16:05") return "siang";
                if (time >= "16:05" && time < "18:30") return "sore";
                if (time >= "18:30" && time <= "22:00") return "malam";
                return "";
            }

            // 3. RENDER & AUTO-PAGINATION (Khusus TV)
            function renderTable() {
                const now = new Date();
                const today = now.toLocaleDateString("id-ID", { weekday: "long" });
                const todayFormatted = today.charAt(0).toUpperCase() + today.slice(1);
                
                daySelect.value = todayFormatted;
                const autoSession = detectCurrentSession();
                sessionSelect.value = autoSession;
                document.getElementById('current-session-name').innerText = autoSession.toUpperCase() || "ISTIRAHAT";

                const limit = parseInt(limitSelect.value);
                const selectedDay = daySelect.value;
                const selectedSession = sessionSelect.value;

                // FILTERING
                let filtered = rows.filter(r => {
                    const d = r.dataset.day;
                    const timeText = r.querySelector(".time")?.innerText.split(" - ")[0].trim() || "00:00";
                    const matchDay = d === selectedDay;
                    let matchSession = true;
                    if (selectedSession === "pagi") matchSession = (timeText >= "07:00" && timeText < "12:20");
                    else if (selectedSession === "siang") matchSession = (timeText >= "12:20" && timeText < "16:05");
                    else if (selectedSession === "sore") matchSession = (timeText >= "16:05" && timeText < "18:30");
                    else if (selectedSession === "malam") matchSession = (timeText >= "18:30" && timeText <= "22:00");
                    return matchDay && matchSession;
                });

                // SORTING
                filtered.sort((a, b) => {
                    const tA = a.querySelector(".time")?.innerText.split(" - ")[0].trim() || "00:00";
                    const tB = b.querySelector(".time")?.innerText.split(" - ")[0].trim() || "00:00";
                    return tA.localeCompare(tB);
                });

                const totalPage = Math.ceil(filtered.length / limit) || 1;
                if (currentPage > totalPage) currentPage = 1;

                const start = (currentPage - 1) * limit;
                const end = start + limit;

                rows.forEach(r => r.style.display = "none");
                filtered.slice(start, end).forEach(r => {
                    r.style.display = "";
                    tbody.appendChild(r);
                });

                pageIndicator.innerText = `Halaman ${currentPage}/${totalPage}`;
            }

            // AUTO CYCLE (Pindah halaman tiap 10 detik biar semua jadwal keliatan)
            setInterval(() => {
                currentPage++;
                renderTable();
            }, 10000);

            // AUTO REFRESH PERGANTIAN SESI
            setInterval(() => {
                const time = new Date().toTimeString().slice(0, 5);
                if (["07:00", "12:20", "16:05", "18:30", "22:00"].includes(time)) location.reload();
            }, 30000);

            renderTable();
            updateClock();
        });
    </script>
</body>
</html>