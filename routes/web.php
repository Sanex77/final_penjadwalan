<?php

use App\Http\Controllers\ProfileController;
use App\Models\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LabController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UserController;



/// --- 1. HALAMAN UMUM ---



Route::get('/', [ScheduleController::class, 'welcome']);

Route::get('/tv-monitor', function () {
    // Ambil semua data jadwal biar di-filter sama JS di view-nya
    $schedules = Schedule::all(); 
    return view('tv.display', compact('schedules'));
})->name('tv.monitor');

Route::get('/jadwal', [ScheduleController::class, 'tvDisplay'])->name('tv.monitor');


// --- 2. LOGIKA REDIRECT DASHBOARD ---
Route::get('/dashboard', function () {
    $role = Auth::user()->role;
    return match ($role) {
        'spv'    => redirect()->route('spv.dashboard'),
        'dosen'  => redirect()->route('dosen.dashboard'),
        'ormawa' => redirect()->route('mahasiswa.dashboard'),
        default  => redirect('/'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// --- 3. GRUP AUTH (HARUS LOGIN) ---
Route::middleware(['auth', 'verified'])->group(function () {

    // A. AREA SUPERVISOR (SPV)
    Route::middleware(['role:spv'])->group(function () {
        Route::get('/spv/dashboard', [ScheduleController::class, 'index'])->name('spv.dashboard');
        Route::post('/spv/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
        Route::delete('/spv/schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
        Route::post('/booking/approve/{id}', [ScheduleController::class, 'approve'])->name('booking.approve');
        Route::post('/schedule/import', [ScheduleController::class, 'importExcel'])->name('schedule.import');
        // Letakkan di dalam group middleware SPV
Route::patch('/spv/schedule/update/{id}', [ScheduleController::class, 'update'])->name('schedule.update');
    });

    // B. AREA DOSEN
    Route::middleware(['role:dosen'])->group(function () {
        Route::get('/dosen/dashboard', [BookingController::class, 'dosenIndex'])->name('dosen.dashboard');
    });

    Route::get('/mahasiswa/dashboard', [BookingController::class, 'mahasiswaIndex'])->name('mahasiswa.dashboard');

    // D. PINTU MASUK SQL (BUAT SEMUA ROLE)
    // Ditaruh di luar middleware 'role' biar gak dicegat middleware yang eror
    Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');

    // E. PROFILE (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::post('/booking/reject/{id}', [ScheduleController::class, 'reject'])->name('booking.reject');
Route::patch('/booking/update-lab/{id}', [BookingController::class, 'updateLab'])->name('booking.updateLab');
Route::patch('/spv/schedule/quick-update/{id}', [ScheduleController::class, 'quickUpdate'])->name('schedule.quickUpdate');

Route::middleware(['auth', 'role:dosen'])->group(function () {
    // ... rute dosen lainnya ...
    Route::post('/check-available-labs', [BookingController::class, 'getAvailableLabs'])->name('labs.check');
});

// Pastikan dibungkus middleware auth dan role spv agar integritas terjaga
Route::middleware(['auth', 'role:spv'])->group(function () {
    Route::post('/spv/lab/store', [LabController::class, 'store'])->name('lab.store');
    Route::delete('/spv/lab/{id}', [LabController::class, 'destroy'])->name('lab.destroy');
});

// Route untuk hapus semua jadwal
Route::delete('/schedule/clear', [App\Http\Controllers\ScheduleController::class, 'clearSchedule'])->name('schedule.clear');
// Pastikan ini ada di dalam group middleware auth
Route::middleware(['auth'])->group(function () {
    Route::post('/subscribe', function (Illuminate\Http\Request $request) {
        // Cek apakah datanya masuk ke sini
        if(!$request->endpoint){
             return response()->json(['error' => 'Data subscription kosong'], 400);
        }

        auth()->user()->updatePushSubscription(
            $request->endpoint,
            $request->publicKey,
            $request->authToken,
            $request->contentEncoding ?? 'aesgcm'
        );

        return response()->json(['success' => true]);
    });

});

Route::get('/dashboard', function () {
    // Ambil data user yang sedang login
    $user = auth()->user();

    // Jadikan "Polisi Lalu Lintas" berdasarkan Role
    if ($user->role === 'spv') {
        return redirect()->action([\App\Http\Controllers\ScheduleController::class, 'dashboard']);; // Arahkan ke rute SPV kamu
    } elseif ($user->role === 'dosen') {
        return redirect()->action([\App\Http\Controllers\BookingController::class, 'dosenIndex']);
    } else {
        return redirect()->action([\App\Http\Controllers\BookingController::class, 'mahasiswaIndex']);
    }
})->middleware(['auth', 'verified'])->name('dashboard');


// 1. Halaman Peringatan (Ditampilkan kalau user belum verifikasi)
Route::get('/email/verify', function () {
    return view('auth.verify-email'); // Kita bikin view-nya di bawah
})->middleware('auth')->name('verification.notice');

// 2. Rute ketika User nge-klik link dari Gmail
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // Proses ubah status di database jadi "Verified"
    return redirect('/dashboard')->with('success', '✅ Email berhasil diverifikasi! Selamat datang.');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 3. Rute untuk tombol "Kirim Ulang Email" (Kalau emailnya nyangkut/nggak masuk)
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('success', 'Link verifikasi baru sudah dikirim ke email kamu!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Rute untuk Import Excel Jadwal Kuliah Asisten
Route::post('/spv/import-asisten', [\App\Http\Controllers\ScheduleController::class, 'importAsistenExcel'])->name('spv.importAsisten');

Route::middleware(['auth', 'role:spv'])->group(function () {
    // Rute ke Halaman Dashboard
    Route::get('/spv/dashboard', [\App\Http\Controllers\ScheduleController::class, 'dashboard'])->name('spv.dashboard');
    
    // Rute ke Halaman Manajemen Jadwal
    Route::get('/spv/jadwal', [\App\Http\Controllers\ScheduleController::class, 'manajemenJadwal'])->name('spv.jadwal');
    Route::post('/spv/jadwal', [ScheduleController::class, 'store'])->name('schedule.store');
    // Rute ke Halaman Jadwal Asisten
    Route::get('/spv/asisten', [\App\Http\Controllers\ScheduleController::class, 'jadwalAsisten'])->name('spv.asisten');

    Route::get('/spv/aprove', [\App\Http\Controllers\ScheduleController::class, 'aproveBoking'])->name('spv.aprove');

    Route::get('/spv/asisten/detail', [ScheduleController::class, 'getDetailAsisten'])->name('spv.asisten');
   // 1. Rute statis (clear) HARUS DI ATAS
Route::delete('/spv/asisten/clear', [ScheduleController::class, 'clearAsistenSchedule'])->name('asisten.clear');

// 2. Rute dinamis ({id}) HARUS DI BAWAH
Route::patch('/spv/asisten/{id}', [ScheduleController::class, 'updateAsisten'])->name('asisten.update');
Route::delete('/spv/asisten/{id}', [ScheduleController::class, 'destroyAsisten'])->name('asisten.destroy');
// Route untuk Tambah Jadwal Asisten Baru
Route::post('/spv/asisten', [ScheduleController::class, 'storeAsisten'])->name('asisten.store');
Route::get('/spv/akun', [RegisteredUserController::class, 'create'])->name('spv.akun');
Route::post('/spv/akun', [RegisteredUserController::class, 'store'])->name('spv.akun.submit');

Route::get('/spv/lab', [\App\Http\Controllers\LabController::class, 'index'])->name('spv.lab');
Route::post('/spv/lab', [\App\Http\Controllers\LabController::class, 'store'])->name('lab.store');
Route::delete('/spv/lab/{id}', [\App\Http\Controllers\LabController::class, 'destroy'])->name('lab.destroy'); 
Route::put('/lab/{id}', [\App\Http\Controllers\LabController::class, 'update'])->name('lab.update');
// Contoh rute di web.php kamu
Route::get('/spv/akun', [UserController::class, 'manajemenAkun'])->name('spv.akun');
});



require __DIR__.'/auth.php';