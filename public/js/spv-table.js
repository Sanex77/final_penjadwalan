document.addEventListener("DOMContentLoaded", () => {
    console.log('🚀 Supervisor Dashboard Ready!');

    // --- META TAGS UNTUK NOTIFIKASI ---
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;

    /* ==========================================
       1. FITUR NOTIFIKASI (WEB PUSH) VIA TOMBOL
    ========================================== */
    const btnNotif = document.getElementById('btnAktifkanNotif');

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    if (btnNotif && 'serviceWorker' in navigator && 'PushManager' in window && vapidPublicKey) {
        navigator.serviceWorker.register('/sw.js').then(swReg => {
            
            btnNotif.addEventListener('click', () => {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        // Kalau diizinkan, jalankan proses langganan
                        swReg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
                        })
                        .then(sub => {
                            fetch('/subscribe', {
                                method: 'POST',
                                body: JSON.stringify(sub),
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                console.log('✅ BERHASIL MASUK DATABASE!', data);
                                alert('🚀 Mantap! Notifikasi Lab sudah aktif.');
                                btnNotif.style.display = 'none'; // Sembunyikan tombol kalau sukses
                            })
                            .catch(err => alert('❌ Gagal nyimpen alamat ke server.'));
                        });
                    } else {
                        alert('Yah, notifikasi diblokir. Buka icon gembok di URL buat buka blokirannya ya!');
                    }
                });
            });

        });
    }

    /* ==========================================
       2. LOGIKA UTAMA TABEL (FILTER, LIMIT, SORT)
    ========================================== */
    const filterDay = document.getElementById('filterDay');
    const filterLab = document.getElementById('filterLab');
    const limitSelect = document.querySelector(".limitSelect");
    const tbody = document.querySelector('#scheduleTable tbody');
    const tableRows = document.querySelectorAll('#scheduleTable tbody tr');
    const noDataMessage = document.getElementById('noDataMessage');

    function updateTable() {
        const selectedDay = filterDay ? filterDay.value : "";
        const selectedLab = filterLab ? filterLab.value : "";
        const limit = limitSelect ? parseInt(limitSelect.value) : 5;
        
        let visibleCount = 0; 
        let showCount = 0;    

        const rowsArray = Array.from(tableRows);

        // PERBAIKAN FATAL: Sorting ngambil data dari input time, bukan dari class indigo
        rowsArray.sort((a, b) => {
            const inputA = a.querySelector('input[name="jam_mulai"]');
            const inputB = b.querySelector('input[name="jam_mulai"]');
            const timeA = inputA ? inputA.value : "00:00";
            const timeB = inputB ? inputB.value : "00:00";
            return timeA.localeCompare(timeB);
        });

        rowsArray.forEach(row => tbody.appendChild(row));

        // Filtering
        rowsArray.forEach(row => {
            const rowHari = row.getAttribute('data-hari');
            const rowLab = row.getAttribute('data-lab');

            const matchDay = !selectedDay || rowHari === selectedDay;
            const matchLab = !selectedLab || rowLab === selectedLab;

            if (matchDay && matchLab) {
                visibleCount++;
                if (showCount < limit) {
                    row.style.display = "";
                    showCount++;
                } else {
                    row.style.display = "none";
                }
            } else {
                row.style.display = "none";
            }
        });

        if (noDataMessage) {
            noDataMessage.style.display = (visibleCount === 0) ? "block" : "none";
        }
    }

    if (filterDay) filterDay.addEventListener('change', updateTable);
    if (filterLab) filterLab.addEventListener('change', updateTable);
    if (limitSelect) limitSelect.addEventListener('change', updateTable);

    updateTable();
    

    /* ==========================================
       3. FITUR UI LAINNYA (Dropdown, Row Click, PDF)
    ========================================== */
    const profileBtn = document.querySelector(".profile-btn");
    const dropdownMenu = document.querySelector(".dropdown-menu");

    if (profileBtn && dropdownMenu) {
        profileBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        });
        document.addEventListener("click", () => dropdownMenu.style.display = "none");
    }

    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            tableRows.forEach(r => r.classList.remove('active-row'));
            this.classList.add('active-row');
        });
    });

    document.querySelectorAll('form').forEach(form => {
        if (form.action.includes('destroy')) {
            form.addEventListener('submit', (e) => {
                if (!confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) e.preventDefault();
            });
        }
    });

    // Fitur PDF
    const btnCetak = document.getElementById('btnCetakPDF');
    if (btnCetak) {
        btnCetak.addEventListener('click', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.text("Laporan Jadwal Lab Komputer", 14, 15);
            doc.setFontSize(10);
            doc.text(`Dicetak pada: ${new Date().toLocaleString('id-ID')}`, 14, 22);

            const rows = [];
            const trs = document.querySelectorAll('#scheduleTable tbody tr');

            trs.forEach(tr => {
                if (tr.style.display !== "none") {
                    // PERBAIKAN: Ambil nilai dari input form, bukan dari innerText TD
                    const tanggal = tr.querySelector('input[name="tanggal"]')?.value || "-";
                    const lab = tr.querySelector('select[name="lab"]')?.value || "-";
                    const matkul = tr.querySelector('input[name="matkul"]')?.value || "-";
                    const dosen = tr.querySelector('input[name="dosen"]')?.value || "-";
                    
                    rows.push([
                        `${tanggal} (${lab})`, 
                        `${matkul} - ${dosen}`
                    ]);
                }
            });

            doc.autoTable({
                head: [['Tanggal & Lab', 'Detail Mata Kuliah']],
                body: rows,
                startY: 30,
                theme: 'grid',
                headStyles: { fillColor: [37, 99, 235] }, 
            });

            doc.save(`Jadwal-Lab-${new Date().getTime()}.pdf`);
        });
        
    }
    
    
});
function toggleBulkManager() {
        const content = document.getElementById('bulk-manager-content');
        const icon = document.getElementById('toggle-icon-bulk');
        
        if (content.style.display === "none") {
            content.style.display = "block";
            icon.style.transform = "rotate(90deg)"; 
        } else {
            content.style.display = "none";
            icon.style.transform = "rotate(0deg)";
        }
    }