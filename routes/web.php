<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;

// Locale Switch Route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
use App\Http\Controllers\DashboardController;

// dashboard pages
Route::get('/', function () {
    return view('pages.dashboard.ecommerce', ['title' => 'Dashboard BAPTwo']);
})->name('dashboard');

// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// pages

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');


// authentication pages
Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->name('signin');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup');

// ui elements pages
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');

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

















