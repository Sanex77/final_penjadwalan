<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Lab ICT</title>
    
    {{-- CSS & Icons --}}
    <link rel="stylesheet" href="{{ asset('css/spv-space.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @yield('styles')

</head>
<body>

    {{-- ================= SIDEBAR ================= --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('img/logo-ubl.png') }}" 
     alt="Foto Profil" 
     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></i>
            <h2>Laboratorium<br>ICT Budi Luhur</h2>
        </div>
        
        <div class="sidebar-menu">
            <a href="{{ route('spv.dashboard') }}" class="{{ request()->routeIs('spv.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="{{ route('spv.jadwal') }}" class="{{ request()->routeIs('spv.jadwal') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> Manajemen Jadwal
            </a>
            <a href="{{ route('spv.aprove') }}" class="{{ request()->routeIs('spv.aprove') ? 'active' : '' }}">
                <i class="fas fa-check-circle"></i> Approve Bookingan
            </a>
            <a href="{{ route('spv.asisten') }}" class="{{ request()->routeIs('spv.asisten') ? 'active' : '' }}">
                <i class="fas fa-user-clock"></i> Jadwal Asisten
            </a>
            <a href="{{ route('spv.lab') }}" class="{{ request()->routeIs('spv.lab') ? 'active' : '' }}">
                <i class="fa fa-desktop"></i> Data Lab
            </a>
            <a href="{{ route('spv.akun') }}" class="{{ request()->routeIs('spv.akun') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i> Pembuatan Akun
            </a>
        </div> 
        
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </form>
        </div>
    </aside>

    {{-- ================= KONTEN UTAMA ================= --}}
    <main class="main-content" id="main-content">
        <div class="topbar">
            <div>
                <button class="burger-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            {{-- PROFIL & DROPDOWN (MAHAL EDITION) --}}
            {{-- PROFIL & DROPDOWN (ANTI-GAGAL) --}}
            <div style="position: relative;" id="profileDropdownContainer">
                
                {{-- Tombol Profil --}}
                <div onclick="toggleProfileDropdown(event)" style="display: flex; align-items: center; gap: 12px; font-weight: bold; color: #334155; cursor: pointer; padding: 6px 14px; border-radius: 12px; transition: 0.2s; border: 1px solid transparent;" onmouseover="this.style.backgroundColor='#f1f5f9'; this.style.borderColor='#e2e8f0';" onmouseout="this.style.backgroundColor='transparent'; this.style.borderColor='transparent';">
                    
                    {{-- Avatar Otomatis dari Nama via UI-Avatars --}}
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'A') }}&background=e0f2fe&color=0284c7&bold=true" 
                         alt="Avatar" 
                         style="width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    
                    <div style="line-height: 1.3;">
                        <div style="font-size: 14px;">SPV Penjadwalan</div>
                        <div style="font-size: 12px; font-weight: 500; color: #64748b;">
                            {{ Auth::user()->name ?? 'Administrator' }} 
                        </div>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 12px; color: #94a3b8; margin-left: 8px;"></i>
                </div>

                {{-- Menu Dropdown (Disembunyikan secara paksa pakai display:none) --}}
                <div id="profileDropdown" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 10px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); width: 220px; overflow: hidden; z-index: 50;">
                    
                    <div style="padding: 16px; border-bottom: 1px solid #f1f5f9; background: #f8fafc;">
                        <div style="font-size: 13px; color: #64748b; font-weight: 600; margin-bottom: 2px;">Masuk sebagai:</div>
                        <div style="font-size: 14px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ Auth::user()->email ?? 'admin@budiluhur.ac.id' }}
                        </div>
                    </div>

                    <a href="{{ route('profile.edit') }}" style="display: flex; align-items: center; padding: 12px 16px; color: #475569; text-decoration: none; font-size: 14px; font-weight: 500; transition: 0.2s;" onmouseover="this.style.backgroundColor='#f1f5f9'; this.style.color='#0ea5e9'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#475569'">
                        <i class="fas fa-user-circle" style="width: 20px; font-size: 16px;"></i> Edit Profil
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0; border-top: 1px solid #f1f5f9;">
                        @csrf
                        <button type="submit" style="width: 100%; text-align: left; background: none; border: none; padding: 12px 16px; color: #ef4444; cursor: pointer; font-size: 14px; font-weight: 500; transition: 0.2s; display: flex; align-items: center;" onmouseover="this.style.backgroundColor='#fef2f2'" onmouseout="this.style.backgroundColor='transparent'">
                            <i class="fas fa-sign-out-alt" style="width: 20px; font-size: 16px;"></i> Log out
                        </button>
                    </form>
                </div>
            </div>
            
        </div>

        <div class="content-area">
            @yield('content')
        </div>
    </main>

    {{-- ================= SCRIPT UMUM ================= --}}
    <script>
        // 1. LOGIKA DROPDOWN PROFIL (SMOOTH)
        // 1. LOGIKA DROPDOWN PROFIL (ANTI GAGAL)
        function toggleProfileDropdown(event) {
            // Mencegah klik tombol terbaca sebagai "klik di luar"
            if(event) event.stopPropagation(); 
            
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }

        // Tutup dropdown jika klik di luar area
        window.addEventListener('click', function(e) {
            const container = document.getElementById('profileDropdownContainer');
            const dropdown = document.getElementById('profileDropdown');
            if (container && !container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // 2. LOGIKA SIDEBAR & BURGER MENU
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            if (sidebar && mainContent) {
                sidebar.classList.toggle('closed');
                mainContent.classList.toggle('expanded');
            }
        }

        // 3. LOGIKA AUTO-CLOSE KHUSUS HALAMAN APPROVE
        document.addEventListener("DOMContentLoaded", function() {
            if (window.location.href.indexOf("aprove") > -1) {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('main-content');
                
                if (sidebar && mainContent) {
                    sidebar.classList.add('closed');
                    mainContent.classList.add('expanded');
                }
            }
            
            // Inisialisasi Flatpickr
            if (typeof flatpickr !== 'undefined' && document.querySelector(".timepicker")) {
                flatpickr(".timepicker", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    minuteIncrement: 15
                });
            }
        });
        
        // 4. FUNGSI TOGGLE COLLAPSE CONTENT
        function toggleLabManager() {
            const content = document.getElementById('lab-manager-content');
            const icon = document.getElementById('toggle-icon');
            if (content && icon) {
                content.style.display = content.style.display === "none" ? "block" : "none";
                icon.style.transform = content.style.display === "none" ? "rotate(0deg)" : "rotate(90deg)";
            }
        }

        function toggleAsistenManager() {
            const content = document.getElementById('asisten-manager-content');
            const icon = document.getElementById('toggle-icon-asisten');
            if (content && icon) {
                content.style.display = content.style.display === "none" ? "block" : "none";
                icon.style.transform = content.style.display === "none" ? "rotate(0deg)" : "rotate(90deg)";
            }
        }

        // 5. LOGIKA PUSH NOTIFICATION
        function initPushNotif() {
            const btnNotif = document.getElementById('btnAktifkanNotif');
            if (btnNotif) {
                btnNotif.addEventListener('click', () => {
                    if (!("Notification" in window)) {
                        alert("Browser ini tidak mendukung notifikasi desktop");
                        return;
                    }
                    Notification.requestPermission().then(permission => {
                        if (permission === 'granted') {
                            if (typeof swReg !== 'undefined') {
                                swReg.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: urlBase64ToUint8Array('{{ env("VAPID_PUBLIC_KEY") }}')
                                }).then(function(subscription) {
                                    fetch('/subscribe', {
                                        method: 'POST',
                                        body: JSON.stringify(subscription),
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    }).then(() => console.log('✅ Alamat browser tersimpan!'));
                                });
                            }
                            alert('Notifikasi berhasil diaktifkan!');
                        } else {
                            alert('Notifikasi diblokir browser.');
                        }
                    });
                });
            }
        }
        initPushNotif();

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) { outputArray[i] = rawData.charCodeAt(i); }
            return outputArray;
        }
    </script>
    
    @yield('scripts')
</body>
</html>