<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    PesananController,
    CuttingQueueController,
    WipTrackingController,
    KapasitasSettingController
};

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth'])->group(function () {
    // Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Pesanan & Cek Kapasitas Real-Time
    Route::get('/pesanan/create', [PesananController::class, 'create'])->name('pesanan.create');
    Route::post('/pesanan', [PesananController::class, 'store'])->name('pesanan.store');
    Route::post('/pesanan/cek-kapasitas', [PesananController::class, 'cekKapasitasAjax'])->name('pesanan.cek-kapasitas');
    Route::patch('/pesanan/{pesanan}/siap-potong', [PesananController::class, 'tandaiSiapPotong'])->name('pesanan.siap-potong');

    // Papan Antrean Cutting & Cetak SPK
    Route::get('/cutting-queue', [CuttingQueueController::class, 'index'])->name('cutting.queue');
    Route::patch('/cutting-queue/{pesanan}/prioritas', [CuttingQueueController::class, 'updatePrioritas'])->name('cutting.prioritas');
    Route::get('/cutting-queue/{pesanan}/cetak-spk', [CuttingQueueController::class, 'cetakSpk'])->name('cutting.cetak-spk');

    // Scan QR & Update Tracking WIP / Vendor Bordir
    Route::get('/scan-wip', [WipTrackingController::class, 'index'])->name('wip.scan');
    Route::get('/scan-wip/cari/{kodeSpk}', [WipTrackingController::class, 'cariSpk'])->name('wip.cari');
    Route::post('/scan-wip/update', [WipTrackingController::class, 'updateStatus'])->name('wip.update');

    // Rute Khusus Role Manajer
    Route::middleware(['role:manajer'])->group(function () {
        Route::patch('/pesanan/{pesanan}/rush-decision', [DashboardController::class, 'keputusanRushOrder'])->name('pesanan.rush-decision');
        Route::get('/pengaturan-kapasitas', [KapasitasSettingController::class, 'index'])->name('kapasitas.index');
        Route::post('/pengaturan-kapasitas/standar', [KapasitasSettingController::class, 'updateStandar'])->name('kapasitas.update-standar');
        Route::post('/pengaturan-kapasitas/penyesuaian', [KapasitasSettingController::class, 'storePenyesuaian'])->name('kapasitas.store-penyesuaian');
        Route::post('/pengaturan-kapasitas/kompleksitas', [KapasitasSettingController::class, 'storeKompleksitas'])->name('kapasitas.store-kompleksitas');
    });
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors(['email' => 'Email atau password salah.']);
})->name('login.post');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');