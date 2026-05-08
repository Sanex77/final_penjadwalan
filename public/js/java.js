document.addEventListener("DOMContentLoaded", () => {
    // 1. SELECTOR
    const dateSelect = document.getElementById("filterDate");
    const labSelect = document.getElementById("filterLab");
    const sessionSelect = document.getElementById("filterSession");
    const limitSelect = document.getElementById("limitSelect");
    const downloadPdfBtn = document.getElementById("downloadPdfBtn"); // Tombol PDF
    
    const tbody = document.getElementById("tableBody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const pageInfo = document.getElementById("pageInfo");

    let currentPage = 1;
    let currentFilteredRows = []; // Simpan data filter buat dicetak ke PDF

    function getTodayISO() {
        const todayObj = new Date();
        const tzOffset = todayObj.getTimezoneOffset() * 60000;
        return (new Date(todayObj - tzOffset)).toISOString().split('T')[0];
    }
    const todayISO = getTodayISO();



    /* ==========================================
       3. CORE LOGIC (FILTER & RENDER)
    ========================================== */
    function renderTable() {
        const limit = parseInt(limitSelect.value);
        const selectedDate = dateSelect.value; 
        const selectedLab = labSelect.value;
        const selectedSession = sessionSelect.value;

        // Sorting waktu
        rows.sort((a, b) => {
            const tA = a.querySelector(".time")?.innerText.split(" - ")[0].trim() || "00:00";
            const tB = b.querySelector(".time")?.innerText.split(" - ")[0].trim() || "00:00";
            return tA.localeCompare(tB);
        });
        rows.forEach(r => tbody.appendChild(r));

        // Filtering
        currentFilteredRows = rows.filter(r => {
            const rowDate = r.dataset.date; 
            const rowLab = r.dataset.lab;
            const timeText = r.querySelector(".time")?.innerText.split(" - ")[0].trim() || "00:00";

            const matchDate = !selectedDate || (rowDate && rowDate.startsWith(selectedDate));
            const matchLab = !selectedLab || rowLab === selectedLab;

            let matchSession = true;
            if (selectedSession) {
                if (selectedSession === "pagi") matchSession = (timeText >= "07:00" && timeText < "12:20");
                else if (selectedSession === "siang") matchSession = (timeText >= "12:20" && timeText < "16:05");
                else if (selectedSession === "sore") matchSession = (timeText >= "16:05" && timeText < "18:30");
                else if (selectedSession === "malam") matchSession = (timeText >= "18:30" && timeText <= "22:00");
            }

            return matchDate && matchLab && matchSession;
        });

        // Pagination
        const totalPage = Math.ceil(currentFilteredRows.length / limit) || 1;
        const start = (currentPage - 1) * limit;
        const end = start + limit;

        rows.forEach(r => r.style.display = "none");
        currentFilteredRows.slice(start, end).forEach(r => r.style.display = "");

        if (pageInfo) pageInfo.innerText = `Page ${currentPage} / ${totalPage}`;
        if (prevBtn) prevBtn.disabled = (currentPage === 1);
        if (nextBtn) nextBtn.disabled = (currentPage === totalPage);
    }

    /* ==========================================
       4. FITUR CETAK PDF
    ========================================== */
    downloadPdfBtn?.addEventListener("click", () => {
        // Panggil library jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4'); // 'l' = Landscape (Biar tabelnya muat)

        // Judul PDF
        doc.setFontSize(16);
        doc.text("Laporan Jadwal Laboratorium FTI", 14, 15);

        // Keterangan Filter
        const tglText = dateSelect.value || "Semua Tanggal";
        const labText = labSelect.value || "Semua Lab";
        doc.setFontSize(10);
        doc.text(`Tanggal: ${tglText} | Ruangan: ${labText}`, 14, 22);

        // Siapkan Data Tabel (Hanya data yang lolos filter)
        const tableData = currentFilteredRows.map(row => {
            const cells = row.querySelectorAll("td");
            // Rapiin format Hari & Tanggal (buang enter/div)
            const hari = cells[1].querySelector("div")?.innerText || "";
            const tgl = cells[1].querySelector("small")?.innerText || "";
            const fullDate = `${hari}, ${tgl}`;

            return [
                cells[0].innerText.trim(), // LAB
                fullDate,                  // DATE
                cells[2].innerText.trim(), // TIME
                cells[3].innerText.trim(), // COURSE
                cells[4].innerText.trim(), // LECTURER
                cells[5].innerText.trim()  // ASISTEN
            ];
        });

        // Generate Tabel di PDF
        doc.autoTable({
            startY: 28,
            head: [['LAB', 'HARI/TANGGAL', 'JAM', 'MATA KULIAH', 'DOSEN', 'ASISTEN']],
            body: tableData,
            theme: 'grid',
            headStyles: { fillColor: [37, 99, 235] }, // Warna biru tema sistem kamu
            styles: { fontSize: 9 },
            columnStyles: {
                3: { cellWidth: 60 } // Lebarin kolom Mata Kuliah biar nggak gampang kepotong
            }
        });

        // Simpan File
        doc.save(`Jadwal_Lab_${tglText}.pdf`);
    });

    /* ==========================================
       5. EVENT LISTENERS
    ========================================== */
    if (dateSelect) {
        dateSelect.addEventListener("change", () => {
            if (dateSelect.value !== todayISO) sessionSelect.value = ""; 
            else sessionSelect.value = detectCurrentSession(); 
            
            currentPage = 1;
            renderTable();
        });
    }

    [labSelect, sessionSelect, limitSelect].forEach(el => {
        el?.addEventListener("change", () => {
            currentPage = 1;
            renderTable();
        });
    });

    nextBtn?.addEventListener("click", () => { currentPage++; renderTable(); });
    prevBtn?.addEventListener("click", () => { if (currentPage > 1) { currentPage--; renderTable(); } });

    // Render Pertama Kali
    renderTable();

    // Auto Refresh
    setInterval(() => {
        if (dateSelect.value === todayISO) {
            const now = new Date();
            const currentTime = now.getHours().toString().padStart(2, '0') + ":" + 
                                now.getMinutes().toString().padStart(2, '0');
            const boundaries = ["07:00", "12:20", "16:05", "18:30", "22:00"];

            if (boundaries.includes(currentTime)) {
                setTimeout(() => location.reload(), 1000);
            }
        }
    }, 30000); 
});