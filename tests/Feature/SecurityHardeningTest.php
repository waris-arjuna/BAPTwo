<?php

use App\Enums\UserRole;
use App\Exports\PerkaraRekapExport;
use App\Models\Dokumen;
use App\Models\User;
use App\Services\MathCaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

uses(RefreshDatabase::class);

test('aplikasi menyertakan header keamanan http standar nosniff, strict-origin, dan sameorigin', function () {
    $response = $this->get('/masuk');

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

test('dokumen privat dilindungi otorisasi per record dan memblokir idor dari guest', function () {
    Storage::fake('local');
    $path = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')->store('dokumen', 'local');

    $dokumen = Dokumen::create([
        'perkara_id' => null,
        'kategori' => 'sprindik',
        'nama_dokumen' => 'Surat Perintah Penyidikan',
        'nama_file_asli' => 'sprindik.pdf',
        'file_path' => $path,
        'file_size' => 1024,
    ]);

    // Guest ditolak dan dialihkan ke masuk
    $resGuestDownload = $this->get(route('dokumen.download', $dokumen));
    $resGuestDownload->assertRedirect(route('masuk'));

    $resGuestPreview = $this->get(route('dokumen.preview', $dokumen));
    $resGuestPreview->assertRedirect(route('masuk'));

    // User berwenang (Penyidik) diizinkan
    $penyidik = User::factory()->create(['role' => UserRole::PENYIDIK]);
    $resAuthDownload = $this->actingAs($penyidik)->get(route('dokumen.download', $dokumen));
    $resAuthDownload->assertOk();

    $resAuthPreview = $this->actingAs($penyidik)->get(route('dokumen.preview', $dokumen));
    $resAuthPreview->assertOk();
    expect($resAuthPreview->headers->get('content-type'))->toBe('application/pdf');
});

test('pesan gagal login bersifat generik untuk mencegah pemetaan akun terdaftar', function () {
    User::factory()->create([
        'email' => 'ada@baptwo.test',
        'password' => 'password123',
    ]);

    // 1. Password salah
    app(MathCaptchaService::class)->generate();
    $resWrongPass = $this->from('/signin')->post('/signin', [
        'email' => 'ada@baptwo.test',
        'password' => 'password-salah',
        'captcha' => (string) session(MathCaptchaService::SESSION_ANSWER_KEY),
    ]);
    $resWrongPass->assertRedirect('/signin');
    $resWrongPass->assertSessionHasErrors(['email' => __('Email atau kata sandi salah.')]);

    // 2. Email tidak terdaftar
    app(MathCaptchaService::class)->generate();
    $resNoEmail = $this->from('/signin')->post('/signin', [
        'email' => 'tidakada@baptwo.test',
        'password' => 'password123',
        'captcha' => (string) session(MathCaptchaService::SESSION_ANSWER_KEY),
    ]);
    $resNoEmail->assertRedirect('/signin');
    $resNoEmail->assertSessionHasErrors(['email' => __('Email atau kata sandi salah.')]);

    // 3. Akun non-aktif: password salah tetap generik (mencegah enumerasi)
    User::factory()->create([
        'email' => 'nonaktif@baptwo.test',
        'password' => 'password123',
        'is_active' => false,
    ]);
    app(MathCaptchaService::class)->generate();
    $resInactiveWrong = $this->from('/signin')->post('/signin', [
        'email' => 'nonaktif@baptwo.test',
        'password' => 'password-salah',
        'captcha' => (string) session(MathCaptchaService::SESSION_ANSWER_KEY),
    ]);
    $resInactiveWrong->assertRedirect('/signin');
    $resInactiveWrong->assertSessionHasErrors(['email' => __('Email atau kata sandi salah.')]);

    // 4. Akun non-aktif: password benar menampilkan banner & toast penonaktifan (D-053)
    app(MathCaptchaService::class)->generate();
    $resInactive = $this->from('/signin')->post('/signin', [
        'email' => 'nonaktif@baptwo.test',
        'password' => 'password123',
        'captcha' => (string) session(MathCaptchaService::SESSION_ANSWER_KEY),
    ]);
    $resInactive->assertRedirect('/signin');
    $resInactive->assertSessionHas('account_inactive');
    $resInactive->assertSessionHas('error');
});

test('ekspor excel menetralisir karakter formula injection dengan tanda petik tunggal', function () {
    $export = new class extends PerkaraRekapExport
    {
        public function testNeutralize(string $input): string
        {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $this->setTextCell($sheet, 'A1', $input);

            return (string) $sheet->getCell('A1')->getValue();
        }
    };

    expect($export->testNeutralize('=SUM(1+1)'))->toBe("'=SUM(1+1)");
    expect($export->testNeutralize('+cmd| /C calc'))->toBe("'+cmd| /C calc");
    expect($export->testNeutralize('-12345'))->toBe("'-12345");
    expect($export->testNeutralize('@SUM(A1:A5)'))->toBe("'@SUM(A1:A5)");
    expect($export->testNeutralize('-'))->toBe('-'); // Dash tunggal tetap aman
    expect($export->testNeutralize('Kasus Biasa'))->toBe('Kasus Biasa');
});
