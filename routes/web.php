<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataPerkaraController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\DpoController;
use App\Http\Controllers\LaporanKejadianController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\MathCaptchaController;
use App\Http\Controllers\P21Controller;
use App\Http\Controllers\PerkaraBerkasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressPerkaraController;
use App\Http\Controllers\PutusanPerkaraController;
use App\Http\Controllers\SpdpController;
use App\Http\Controllers\SprindikController;
use App\Http\Controllers\Tahap1Controller;
use App\Http\Controllers\Tahap2Controller;
use App\Http\Controllers\TahapanPerkaraController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

// Locale Switch Route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Captcha
Route::get('/captcha/refresh', [MathCaptchaController::class, 'refresh'])->name('captcha.refresh');

// Error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// Guest authentication pages
Route::middleware('guest')->group(function () {
    Route::get('/signin', [AuthController::class, 'showSignin'])->name('signin');
    Route::post('/signin', [AuthController::class, 'login'])->name('signin.attempt');

    Route::get('/signup', function () {
        return view('pages.auth.signup', ['title' => __('Sign Up')]);
    })->name('signup');
});

// Authenticated application pages
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::put('/profile/account', [ProfileController::class, 'updateAccount'])->name('profile.account.update');
    Route::put('/profile/personal', [ProfileController::class, 'updatePersonal'])->name('profile.personal.update');

    // Wilayah Proxy (api.kodewilayah.web.id)
    Route::get('/wilayah/provinsi', [WilayahController::class, 'provinsi'])->name('wilayah.provinsi');
    Route::get('/wilayah/kabupaten/{provinsiKode}', [WilayahController::class, 'kabupaten'])->name('wilayah.kabupaten');
    Route::get('/wilayah/kecamatan/{kabupatenKode}', [WilayahController::class, 'kecamatan'])->name('wilayah.kecamatan');
    Route::get('/wilayah/desa/{kecamatanKode}', [WilayahController::class, 'desa'])->name('wilayah.desa');

    // Master Data (6 Submenus: Semua role bisa lihat; Penyidik & Admin bisa tambah/ubah/soft-delete)
    Route::get('/master-data', [MasterDataController::class, 'redirectDefault'])->name('master-data.redirect');
    Route::get('/master-data/{type}', [MasterDataController::class, 'index'])->name('master-data.index');
    Route::middleware('can:manage-master-data')->group(function () {
        Route::post('/master-data/{type}', [MasterDataController::class, 'store'])->name('master-data.store');
        Route::put('/master-data/{type}/{id}', [MasterDataController::class, 'update'])->name('master-data.update');
        Route::delete('/master-data/{type}/{id}', [MasterDataController::class, 'destroy'])->name('master-data.destroy');
        Route::post('/master-data/{type}/{id}/restore', [MasterDataController::class, 'restore'])->name('master-data.restore');
    });

    // Manajemen Pengguna (Administrator only — UC-05)
    Route::middleware('can:manage-users')->group(function () {
        Route::get('/pengaturan', [UserController::class, 'index'])->name('users.index');
        Route::post('/pengaturan', [UserController::class, 'store'])->name('users.store');
        Route::put('/pengaturan/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/pengaturan/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/pengaturan/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    });

    // Laporan Kejadian
    Route::get('/laporan-kejadian', [LaporanKejadianController::class, 'index'])->name('laporan-kejadian.index');
    Route::get('/laporan-kejadian/tambah', [LaporanKejadianController::class, 'create'])->middleware('can:create-cases')->name('laporan-kejadian.create');
    Route::post('/laporan-kejadian', [LaporanKejadianController::class, 'store'])->middleware('can:create-cases')->name('laporan-kejadian.store');
    Route::get('/laporan-kejadian/{perkara}', [LaporanKejadianController::class, 'show'])->name('laporan-kejadian.show');
    Route::get('/laporan-kejadian/{perkara}/edit', [LaporanKejadianController::class, 'edit'])->middleware('can:edit-draft')->name('laporan-kejadian.edit');
    Route::put('/laporan-kejadian/{perkara}', [LaporanKejadianController::class, 'update'])->middleware('can:edit-draft')->name('laporan-kejadian.update');
    Route::delete('/laporan-kejadian/{perkara}', [LaporanKejadianController::class, 'destroy'])->middleware('can:soft-delete')->name('laporan-kejadian.destroy');

    // Sprindik
    Route::get('/sprindik', [SprindikController::class, 'index'])->name('sprindik.index');
    Route::get('/sprindik/tambah', [SprindikController::class, 'create'])->middleware('can:create-cases')->name('sprindik.create');
    Route::post('/sprindik', [SprindikController::class, 'store'])->middleware('can:create-cases')->name('sprindik.store');
    Route::get('/sprindik/{perkara}', [SprindikController::class, 'show'])->name('sprindik.show');
    Route::get('/sprindik/{perkara}/edit', [SprindikController::class, 'edit'])->middleware('can:edit-draft')->name('sprindik.edit');
    Route::put('/sprindik/{perkara}', [SprindikController::class, 'update'])->middleware('can:edit-draft')->name('sprindik.update');
    Route::delete('/sprindik/{perkara}', [SprindikController::class, 'destroy'])->middleware('can:soft-delete')->name('sprindik.destroy');

    // Data Perkara & Berkas Perkara Pra-SPDP (TKP, PPNS, Tersangka, Berkas, Pasal Berlapis)
    Route::get('/data-perkara', [DataPerkaraController::class, 'index'])->name('data-perkara.index');
    Route::get('/progress-perkara', [ProgressPerkaraController::class, 'index'])->name('progress-perkara.index');
    Route::get('/perkara/{perkara}', [PerkaraBerkasController::class, 'show'])->name('perkara.show');
    Route::get('/perkara/{perkara}/buat', [PerkaraBerkasController::class, 'edit'])->middleware('can:edit-draft')->name('perkara.edit');
    Route::put('/perkara/{perkara}/buat', [PerkaraBerkasController::class, 'update'])->middleware('can:edit-draft')->name('perkara.update');

    // Dokumen (Download & Preview Privat)
    Route::get('/dokumen/{dokumen}/download', [DokumenController::class, 'download'])->name('dokumen.download');
    Route::get('/dokumen/{dokumen}/preview', [DokumenController::class, 'preview'])->name('dokumen.preview');

    // Tahapan Progress Berkas Perkara (PRD F-10 s.d. F-19)
    Route::prefix('berkas-perkara/{berkas}')->name('berkas-perkara.')->group(function () {
        Route::get('/tahapan/tambah', [TahapanPerkaraController::class, 'create'])->middleware('can:create-cases')->name('tahapan.create');
        Route::post('/tahapan', [TahapanPerkaraController::class, 'store'])->middleware('can:create-cases')->name('tahapan.store');
        Route::get('/tahapan/{tahapan}', [TahapanPerkaraController::class, 'show'])->name('tahapan.show');
        Route::get('/tahapan/{tahapan}/edit', [TahapanPerkaraController::class, 'edit'])->middleware('can:edit-draft')->name('tahapan.edit');
        Route::put('/tahapan/{tahapan}', [TahapanPerkaraController::class, 'update'])->middleware('can:edit-draft')->name('tahapan.update');

        // DPO (Aktivasi & Pencabutan - PRD F-24 s.d. F-27)
        Route::post('/dpo', [DpoController::class, 'store'])->middleware('can:toggle-dpo')->name('dpo.store');
        Route::post('/dpo/cabut', [DpoController::class, 'destroy'])->middleware('can:toggle-dpo')->name('dpo.destroy');
    });

    // SPDP (Data Riil & Sub-Tahap Opsional P-16, P-17P)
    Route::get('/spdp', [SpdpController::class, 'index'])->name('spdp.index');
    Route::get('/spdp/tambah', [SpdpController::class, 'create'])->middleware('can:create-cases')->name('spdp.create');
    Route::get('/spdp/{id}', [SpdpController::class, 'show'])->name('spdp.show');

    // TAHAP 1 (Data Riil & Sub-Tahap Opsional P-18, P-19)
    Route::get('/tahap-1', [Tahap1Controller::class, 'index'])->name('tahap-1.index');
    Route::get('/tahap-1/tambah', [Tahap1Controller::class, 'create'])->middleware('can:create-cases')->name('tahap-1.create');
    Route::get('/tahap-1/{id}', [Tahap1Controller::class, 'show'])->name('tahap-1.show');

    // P-21 (Data Riil)
    Route::get('/p21', [P21Controller::class, 'index'])->name('p21.index');
    Route::get('/p21/tambah', [P21Controller::class, 'create'])->middleware('can:create-cases')->name('p21.create');
    Route::get('/p21/{id}', [P21Controller::class, 'show'])->name('p21.show');

    // TAHAP 2 (Data Riil, Pelimpahan & Finalisasi Status Selesai)
    Route::get('/tahap-2', [Tahap2Controller::class, 'index'])->name('tahap-2.index');
    Route::get('/tahap-2/tambah', [Tahap2Controller::class, 'create'])->middleware('can:create-cases')->name('tahap-2.create');
    Route::get('/tahap-2/{id}', [Tahap2Controller::class, 'show'])->name('tahap-2.show');

    // PUTUSAN PERKARA (Keputusan Pengadilan)
    Route::get('/putusan-perkara', [PutusanPerkaraController::class, 'index'])->name('putusan-perkara.index');
    Route::get('/putusan-perkara/tambah', [PutusanPerkaraController::class, 'create'])->middleware('can:create-cases')->name('putusan-perkara.create');
    Route::get('/putusan-perkara/{id}', [PutusanPerkaraController::class, 'show'])->name('putusan-perkara.show');
});
