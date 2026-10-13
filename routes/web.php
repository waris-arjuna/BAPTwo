<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MathCaptchaController;
use App\Http\Controllers\ProfileController;
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
    Route::get('/', function () {
        return view('pages.dashboard.ecommerce', ['title' => 'Dashboard BAPTwo']);
    })->name('dashboard');

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

    // Manajemen Pengguna (Administrator only — UC-05)
    Route::middleware('can:manage-users')->group(function () {
        Route::get('/pengaturan', [UserController::class, 'index'])->name('users.index');
        Route::post('/pengaturan', [UserController::class, 'store'])->name('users.store');
        Route::put('/pengaturan/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/pengaturan/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/pengaturan/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    });

    // Laporan Kejadian
    Route::get('/laporan-kejadian', function () {
        return view('pages.laporan-kejadian.index');
    })->name('laporan-kejadian.index');

    Route::get('/laporan-kejadian/tambah', function () {
        return view('pages.laporan-kejadian.create');
    })->middleware('can:create-cases')->name('laporan-kejadian.create');

    Route::get('/laporan-kejadian/{id}', function ($id) {
        return view('pages.laporan-kejadian.show', [
            'id' => $id,
        ]);
    })->name('laporan-kejadian.show');

    // Sprindik
    Route::get('/sprindik', function () {
        return view('pages.sprindik.index');
    })->name('sprindik.index');

    Route::get('/sprindik/tambah', function () {
        return view('pages.sprindik.create');
    })->middleware('can:create-cases')->name('sprindik.create');

    Route::get('/sprindik/{id}', function ($id) {
        return view('pages.sprindik.show', [
            'id' => $id,
        ]);
    })->name('sprindik.show');

    // SPDP
    Route::get('/spdp', function () {
        return view('pages.spdp.index');
    })->name('spdp.index');

    Route::get('/spdp/tambah', function () {
        return view('pages.spdp.create');
    })->middleware('can:create-cases')->name('spdp.create');

    Route::get('/spdp/{id}', function ($id) {
        return view('pages.spdp.show', [
            'id' => $id,
        ]);
    })->name('spdp.show');

    // TAHAP 1
    Route::get('/tahap-1', function () {
        return view('pages.tahap-1.index');
    })->name('tahap-1.index');

    Route::get('/tahap-1/tambah', function () {
        return view('pages.tahap-1.create');
    })->middleware('can:create-cases')->name('tahap-1.create');

    Route::get('/tahap-1/{id}', function ($id) {
        return view('pages.tahap-1.show', [
            'id' => $id,
        ]);
    })->name('tahap-1.show');

    // P-21
    Route::get('/p21', function () {
        return view('pages.p21.index');
    })->name('p21.index');

    Route::get('/p21/tambah', function () {
        return view('pages.p21.create');
    })->middleware('can:create-cases')->name('p21.create');

    Route::get('/p21/{id}', function ($id) {
        return view('pages.p21.show', [
            'id' => $id,
        ]);
    })->name('p21.show');

    // TAHAP 2
    Route::get('/tahap-2', function () {
        return view('pages.tahap-2.index');
    })->name('tahap-2.index');

    Route::get('/tahap-2/tambah', function () {
        return view('pages.tahap-2.create');
    })->middleware('can:create-cases')->name('tahap-2.create');

    Route::get('/tahap-2/{id}', function ($id) {
        return view('pages.tahap-2.show', [
            'id' => $id,
        ]);
    })->name('tahap-2.show');
});
