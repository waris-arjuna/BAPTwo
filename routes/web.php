<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MathCaptchaController;
use Illuminate\Support\Facades\Route;

// Locale Switch Route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// dashboard pages
Route::get('/', function () {
    return view('pages.dashboard.ecommerce', ['title' => 'Dashboard BAPTwo']);
})->name('dashboard');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// authentication pages
Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => __('Sign In')]);
})->name('signin');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => __('Sign Up')]);
})->name('signup');

// Captcha
Route::get('/captcha/refresh', [MathCaptchaController::class, 'refresh'])->name('captcha.refresh');

// Laporan Kejadian
Route::get('/laporan-kejadian', function () {
    return view('pages.laporan-kejadian.index');
})->name('laporan-kejadian.index');

Route::get('/laporan-kejadian/tambah', function () {
    return view('pages.laporan-kejadian.create');
})->name('laporan-kejadian.create');

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
})->name('sprindik.create');

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
})->name('spdp.create');

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
})->name('tahap-1.create');

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
})->name('p21.create');

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
})->name('tahap-2.create');

Route::get('/tahap-2/{id}', function ($id) {
    return view('pages.tahap-2.show', [
        'id' => $id,
    ]);
})->name('tahap-2.show');
