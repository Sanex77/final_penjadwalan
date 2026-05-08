document.addEventListener("DOMContentLoaded", function () {
    const roleSelect = document.getElementById("role-select");
    const sections = {
        dosen: document.getElementById("form-dosen"),
        ormawa: document.getElementById("form-ormawa"),
        spv: document.getElementById("form-spv")};

    const formDosen = document.getElementById("form-dosen");
    const formOrmawa = document.getElementById("form-ormawa");
    const formSpv = document.getElementById("form-spv");

    const emailLabel = document.getElementById("label-email");
    const emailHint = document.getElementById("email-hint");

    const btnCek = document.getElementById('btnCekLab');
    const selectLab = document.getElementById('select_lab');
    const btnSubmit = document.getElementById('btnSubmitBooking');


    function resetForms() {
        formDosen.style.display = "none";
        formOrmawa.style.display = "none";
        formSpv.style.display = "none";
    }

    function updateForm() {
        const role = roleSelect.value;

        resetForms();

        if (role === "dosen") {
            formDosen.style.display = "block";
            emailLabel.innerText = "Email Dosen";
            emailHint.innerText = "*Gunakan email institusi dosen";
        }

        if (role === "ormawa") {
            formOrmawa.style.display = "block";
            emailLabel.innerText = "Email Organisasi";
            emailHint.innerText = "*Gunakan email aktif perwakilan";
        }

        if (role === "spv") {
            formSpv.style.display = "block";
            emailLabel.innerText = "Email Supervisor";
            emailHint.innerText = "*Gunakan email resmi SPV";
        }
    }

    roleSelect.addEventListener("change", updateForm);

    // Jalankan pertama kali
    updateForm();

    function disableAllInputs() {
    document.querySelectorAll(".role-section input, .role-section textarea")
        .forEach(el => el.disabled = true);
}

function enableInputs(section) {
    section.querySelectorAll("input, textarea")
        .forEach(el => el.disabled = false);
}

function updateForm() {
    const role = roleSelect.value;

    resetForms();
    disableAllInputs();

    if (role === "dosen") {
        formDosen.style.display = "block";
        enableInputs(formDosen);
    }

    if (role === "ormawa") {
        formOrmawa.style.display = "block";
        enableInputs(formOrmawa);
    }

    if (role === "spv") {
        formSpv.style.display = "block";
        enableInputs(formSpv);
    }
}

function cekDosen() {
    if (btnCek) {
        btnCek.addEventListener('click', async function() {
            const tanggal = document.getElementById('check_tanggal').value;
            const mulai = document.getElementById('check_mulai').value;
            const selesai = document.getElementById('check_selesai').value;

            if (!tanggal || !mulai || !selesai) {
                alert('⚠️ Isi Tanggal dan Jam dulu ya, Pak/Bu Dosen!');
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
                    // Reset dropdown
                    selectLab.innerHTML = '';
                    
                    if (data.labs.length > 0) {
                        // Isi dengan Lab yang kosong
                        data.labs.forEach(lab => {
                            const option = document.createElement('option');
                            option.value = lab;
                            option.text = lab.toUpperCase();
                            selectLab.appendChild(option);
                        });
                        
                        selectLab.disabled = false;
                        btnSubmit.disabled = false;
                        btnSubmit.style.opacity = '1';
                        btnCek.innerText = '✅ Lab Ditemukan';
                        btnCek.style.background = 'var(--success)';
                    } else {
                        selectLab.innerHTML = '<option value="">Semua Lab Penuh!</option>';
                        selectLab.disabled = true;
                        btnSubmit.disabled = true;
                        btnCek.innerText = '❌ Lab Penuh';
                        btnCek.style.background = 'var(--danger)';
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem.');
            }
        });
    }
}


});