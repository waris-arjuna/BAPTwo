<?php

use App\Enums\UserRole;
use App\Models\Alamat;
use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\Panggol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('halaman profil menampilkan 4 card lengkap beserta format Pangkat dan Golongan', function () {
    $jabatan = Jabatan::create(['nama_jabatan' => 'Penyidik Pegawai Negeri Sipil']);
    $bidang = Bidang::create(['nama_bidang' => 'Bidang Penindakan']);
    $panggol = Panggol::create(['pangkat' => 'Pembina', 'golongan' => 'IV/a']);
    $alamat = Alamat::create([
        'provinsi_kode' => '63',
        'provinsi_nama' => 'Kalimantan Selatan',
        'kabupaten_kode' => '6371',
        'kabupaten_nama' => 'Kota Banjarmasin',
        'kecamatan_kode' => '637104',
        'kecamatan_nama' => 'Banjarmasin Utara',
        'desa_kode' => '6371041006',
        'desa_nama' => 'Pangeran',
        'rt' => '004',
        'rw' => '001',
        'alamat_detail' => 'Jl. Brigjend H. Hasan Basri No. 40',
    ]);

    $user = User::factory()->create([
        'nama' => 'Budi Santoso',
        'nip' => '198801012014011001',
        'email' => 'budi@baptwo.test',
        'role' => UserRole::PENYIDIK,
        'jabatan_id' => $jabatan->id,
        'bidang_id' => $bidang->id,
        'panggol_id' => $panggol->id,
        'alamat_id' => $alamat->id,
        'tempat_lahir' => 'Banjarmasin',
        'tanggal_lahir' => '1988-01-01',
        'jenis_kelamin' => 'laki-laki',
        'status_pernikahan' => 'kawin',
        'no_telp' => '081250001111',
    ]);

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertOk()
        ->assertSee('Ganti Foto Profil')
        ->assertSee('Informasi Akun')
        ->assertSee('Update Email / Password')
        ->assertSee('Minimal 8 karakter.')
        ->assertSee('Kombinasi angka dan huruf (besar dan kecil).')
        ->assertSee('Gunakan simbol')
        ->assertSee('Data Diri')
        ->assertSee('Budi Santoso')
        ->assertSee('Banjarmasin')
        ->assertSee('081250001111')
        ->assertSee('Jl. Brigjend H. Hasan Basri No. 40')
        ->assertSee('Informasi Kepegawaian')
        ->assertSee('198801012014011001')
        ->assertSee('Penyidik Pegawai Negeri Sipil')
        ->assertSee('Bidang Penindakan')
        ->assertSee('Pembina (IV/a)')
        ->assertSee('Penyidik');
});

test('pengguna dapat mengganti foto profil dengan gambar valid dan ditolak jika bukan gambar', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    // File bukan gambar ditolak
    $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');
    $this->actingAs($user)
        ->post(route('profile.photo.update'), ['foto_profil' => $invalidFile])
        ->assertSessionHasErrors('foto_profil');

    // File gambar valid berhasil disimpan (1x1 PNG tanpa ketergantungan ekstensi GD)
    $pngBytes = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    $validImage = UploadedFile::fake()->createWithContent('avatar.png', $pngBytes);
    $response = $this->actingAs($user)
        ->post(route('profile.photo.update'), ['foto_profil' => $validImage]);

    $response->assertRedirect()
        ->assertSessionHas('profile_success');
    expect(session('profile_success')['title'])->toBe('Foto Profil Diperbarui');

    $user->refresh();
    expect($user->foto_profil)->not->toBeNull();
    Storage::disk('public')->assertExists($user->foto_profil);
    expect($user->fotoProfilUrl())->toContain('/storage/'.$user->foto_profil);
});

test('update akun memvalidasi password lama dan syarat kompleksitas password baru', function () {
    $user = User::factory()->create([
        'email' => 'lama@baptwo.test',
        'password' => Hash::make('PasswordLama123!'),
    ]);

    // 1. Password lama salah -> gagal
    $this->actingAs($user)
        ->put(route('profile.account.update'), [
            'email' => 'baru@baptwo.test',
            'current_password' => 'SalahPassword!',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertSessionHasErrors('current_password');

    // 2. Password baru kurang dari 8 karakter / tanpa simbol / tanpa huruf besar -> gagal
    $this->actingAs($user)
        ->put(route('profile.account.update'), [
            'email' => 'baru@baptwo.test',
            'current_password' => 'PasswordLama123!',
            'password' => 'lemah123',
            'password_confirmation' => 'lemah123',
        ])
        ->assertSessionHasErrors('password');

    // 3. Password baru memenuhi semua syarat -> berhasil
    $response = $this->actingAs($user)
        ->put(route('profile.account.update'), [
            'email' => 'baru@baptwo.test',
            'current_password' => 'PasswordLama123!',
            'password' => 'PasswordBaru99#',
            'password_confirmation' => 'PasswordBaru99#',
        ]);

    $response->assertRedirect()
        ->assertSessionHas('profile_success');

    $user->refresh();
    expect($user->email)->toBe('baru@baptwo.test')
        ->and(Hash::check('PasswordBaru99#', $user->password))->toBeTrue();
});

test('update data diri hanya mengubah no_telp dan alamat serta mengabaikan field terkunci', function () {
    $user = User::factory()->create([
        'nama' => 'Nama Asli Tetap',
        'nip' => '199001012020011009',
        'tempat_lahir' => 'Martapura',
        'tanggal_lahir' => '1990-01-01',
        'jenis_kelamin' => 'laki-laki',
        'status_pernikahan' => 'kawin',
        'no_telp' => '081100000000',
    ]);

    $response = $this->actingAs($user)
        ->put(route('profile.personal.update'), [
            'nama' => 'Nama Bajakan',
            'nip' => '999999999999999999',
            'tempat_lahir' => 'Jakarta',
            'no_telp' => '081299998888',
            'provinsi_kode' => '63',
            'provinsi_nama' => 'Kalimantan Selatan',
            'kabupaten_kode' => '6371',
            'kabupaten_nama' => 'Kota Banjarmasin',
            'kecamatan_kode' => '637104',
            'kecamatan_nama' => 'Banjarmasin Utara',
            'desa_kode' => '6371041006',
            'desa_nama' => 'Pangeran',
            'rt' => '005',
            'rw' => '002',
            'alamat_detail' => 'Jl. Sultan Adam No. 12',
        ]);

    $response->assertRedirect()
        ->assertSessionHas('profile_success');

    $user->refresh();
    expect($user->nama)->toBe('Nama Asli Tetap')
        ->and($user->nip)->toBe('199001012020011009')
        ->and($user->tempat_lahir)->toBe('Martapura')
        ->and($user->no_telp)->toBe('081299998888')
        ->and($user->alamat)->not->toBeNull()
        ->and($user->alamat->alamat_detail)->toBe('Jl. Sultan Adam No. 12')
        ->and($user->alamat->kabupaten_nama)->toBe('Kota Banjarmasin');
});

test('endpoint proxy wilayah mengembalikan daftar wilayah berjenjang', function () {
    Http::fake([
        'https://api.kodewilayah.web.id/*' => Http::response([
            'success' => true,
            'data' => [
                ['code' => 63, 'name' => 'Kalimantan Selatan'],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();

    $res = $this->actingAs($user)
        ->getJson(route('wilayah.provinsi'))
        ->assertOk()
        ->assertJsonPath('success', true);

    $kalsel = collect($res->json('data'))->firstWhere('code', '63');
    expect($kalsel)->not->toBeNull()
        ->and($kalsel['name'])->toBe('Kalimantan Selatan');
});

test('halaman profil merender tombol dan modal pratinjau foto profil', function () {
    $user = User::factory()->create([
        'nama' => 'Ahmad Dahlan',
        'role' => UserRole::PENYIDIK,
    ]);

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertOk()
        ->assertSee('Lihat Foto')
        ->assertSee('isPhotoModalOpen')
        ->assertSee('Foto Profil')
        ->assertSee('Tutup');
});
