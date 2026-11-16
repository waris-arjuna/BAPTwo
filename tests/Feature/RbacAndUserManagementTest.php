<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

it('enforces PRD RBAC matrix across Penyidik, Pimpinan, and Administrator', function () {
    $penyidik = User::factory()->penyidik()->make(['id' => 1]);
    $pimpinan = User::factory()->pimpinan()->make(['id' => 2]);
    $admin = User::factory()->administrator()->make(['id' => 3]);

    // Lihat semua perkara
    expect(Gate::forUser($penyidik)->allows('view-cases'))->toBeTrue()
        ->and(Gate::forUser($pimpinan)->allows('view-cases'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('view-cases'))->toBeTrue();

    // Tambah berkas/perkara
    expect(Gate::forUser($penyidik)->allows('create-cases'))->toBeTrue()
        ->and(Gate::forUser($pimpinan)->allows('create-cases'))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('create-cases'))->toBeTrue();

    // Edit saat masih Draft ($isFinalized = false)
    expect(Gate::forUser($penyidik)->allows('edit-draft', false))->toBeTrue()
        ->and(Gate::forUser($pimpinan)->allows('edit-draft', false))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('edit-draft', false))->toBeTrue();

    // Edit setelah tahap difinalkan ($isFinalized = true) — terkunci mutlak termasuk untuk Administrator
    expect(Gate::forUser($penyidik)->allows('edit-draft', true))->toBeFalse()
        ->and(Gate::forUser($pimpinan)->allows('edit-draft', true))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('edit-draft', true))->toBeFalse();

    // Soft delete
    expect(Gate::forUser($penyidik)->allows('soft-delete'))->toBeTrue()
        ->and(Gate::forUser($pimpinan)->allows('soft-delete'))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('soft-delete'))->toBeTrue();

    // Hard delete dilarang untuk semua role
    expect(Gate::forUser($penyidik)->allows('hard-delete'))->toBeFalse()
        ->and(Gate::forUser($pimpinan)->allows('hard-delete'))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('hard-delete'))->toBeFalse()
        ->and($admin->can('forceDelete', $penyidik))->toBeFalse();
});

it('blocks Pimpinan from accessing create routes while allowing Penyidik and Administrator', function () {
    $penyidik = User::factory()->penyidik()->make();
    $pimpinan = User::factory()->pimpinan()->make();
    $admin = User::factory()->administrator()->make();

    foreach (['/laporan-kejadian/tambah', '/sprindik/tambah'] as $formRoute) {
        $this->actingAs($pimpinan)->get($formRoute)->assertForbidden();
        $this->actingAs($penyidik)->get($formRoute)->assertOk();
        $this->actingAs($admin)->get($formRoute)->assertOk();
    }

    foreach (['/spdp/tambah' => '/spdp', '/tahap-1/tambah' => '/tahap-1', '/p21/tambah' => '/p21', '/tahap-2/tambah' => '/tahap-2'] as $stageRoute => $indexRoute) {
        $this->actingAs($pimpinan)->get($stageRoute)->assertForbidden();
        $this->actingAs($penyidik)->get($stageRoute)->assertRedirect($indexRoute);
        $this->actingAs($admin)->get($stageRoute)->assertRedirect($indexRoute);
    }
});

it('hides Tambah, Edit, and stage advancement buttons from Pimpinan across index and detail views', function () {
    $this->seedBaselineCasesAndStages();
    $penyidik = User::factory()->penyidik()->make();
    $pimpinan = User::factory()->pimpinan()->make();

    // Laporan Kejadian Index & Show
    $this->actingAs($pimpinan)->get('/laporan-kejadian')
        ->assertOk()
        ->assertDontSee('Tambah Laporan Kejadian')
        ->assertDontSee('Input Sprindik')
        ->assertDontSee('Buat Perkara');
    $this->actingAs($penyidik)->get('/laporan-kejadian')
        ->assertOk()
        ->assertSee('Tambah Laporan Kejadian')
        ->assertSee('Input Sprindik');

    $this->actingAs($pimpinan)->get('/laporan-kejadian/1')
        ->assertOk()
        ->assertDontSee('Input Sprindik');
    $this->actingAs($penyidik)->get('/laporan-kejadian/1')
        ->assertOk()
        ->assertSee('Input Sprindik');

    // Sprindik Index & Show
    $this->actingAs($pimpinan)->get('/sprindik')
        ->assertOk()
        ->assertDontSee('Tambah Sprindik')
        ->assertDontSee('Buat Perkara');
    $this->actingAs($penyidik)->get('/sprindik')
        ->assertOk()
        ->assertSee('Tambah Sprindik')
        ->assertSee('Buat Perkara');

    $this->actingAs($pimpinan)->get('/sprindik/2')
        ->assertOk()
        ->assertDontSee('Buat Perkara');
    $this->actingAs($penyidik)->get('/sprindik/2')
        ->assertOk()
        ->assertSee('Buat Perkara');

    // SPDP Index & Show
    $this->actingAs($pimpinan)->get('/spdp')
        ->assertOk()
        ->assertDontSee('Tambah SPDP')
        ->assertDontSee('Mulai Tahap 1');
    $this->actingAs($penyidik)->get('/spdp')
        ->assertOk()
        ->assertSee('Tambah SPDP')
        ->assertSee('Mulai Tahap 1');

    $this->actingAs($pimpinan)->get('/spdp/1')
        ->assertRedirect(route('berkas-perkara.tahapan.show', ['berkas' => 1, 'tahapan' => 1]));
    $this->actingAs($penyidik)->get('/spdp/1')
        ->assertRedirect(route('berkas-perkara.tahapan.show', ['berkas' => 1, 'tahapan' => 1]));

    // Tahap 1 Index & Show
    $this->actingAs($pimpinan)->get('/tahap-1')
        ->assertOk()
        ->assertDontSee('Tambah Tahap 1')
        ->assertDontSee('Lanjut P-21');
    $this->actingAs($penyidik)->get('/tahap-1')
        ->assertOk()
        ->assertSee('Tambah Tahap 1')
        ->assertSee('Lanjut P-21');

    $this->actingAs($pimpinan)->get('/tahap-1/2')
        ->assertRedirect(route('berkas-perkara.tahapan.show', ['berkas' => 1, 'tahapan' => 2]));
    $this->actingAs($penyidik)->get('/tahap-1/2')
        ->assertRedirect(route('berkas-perkara.tahapan.show', ['berkas' => 1, 'tahapan' => 2]));

    // P-21 Index & Show
    $this->actingAs($pimpinan)->get('/p21')
        ->assertOk()
        ->assertDontSee('Tambah P-21 / P-21a');
    $this->actingAs($penyidik)->get('/p21')
        ->assertOk()
        ->assertSee('Tambah P-21 / P-21a');

    $this->actingAs($pimpinan)->get('/p21/3')
        ->assertRedirect(route('berkas-perkara.tahapan.show', ['berkas' => 1, 'tahapan' => 3]));
    $this->actingAs($penyidik)->get('/p21/3')
        ->assertRedirect(route('berkas-perkara.tahapan.show', ['berkas' => 1, 'tahapan' => 3]));

    // Canonical Tahapan Show RBAC
    $this->actingAs($pimpinan)->get(route('berkas-perkara.tahapan.show', ['berkas' => 1, 'tahapan' => 1]))
        ->assertOk()
        ->assertDontSee('Kelola P-16');
    $this->actingAs($penyidik)->get(route('berkas-perkara.tahapan.show', ['berkas' => 1, 'tahapan' => 1]))
        ->assertOk()
        ->assertSee('Kelola P-16');
});

it('restricts user management routes to Administrator only', function () {
    $penyidik = User::factory()->penyidik()->create();
    $pimpinan = User::factory()->pimpinan()->create();
    $admin = User::factory()->administrator()->create();

    $this->actingAs($penyidik)->get('/pengaturan')->assertForbidden();
    $this->actingAs($pimpinan)->get('/pengaturan')->assertForbidden();
    $this->actingAs($admin)->get('/pengaturan')->assertOk();
});

it('allows Administrator to create, update, soft delete, and restore users', function () {
    $admin = User::factory()->administrator()->create();

    // Create user
    $this->actingAs($admin)->post('/pengaturan', [
        'nama' => 'Budi Santoso',
        'nip' => '199505052020011005',
        'email' => 'budi@baptwo.test',
        'role' => UserRole::PENYIDIK->value,
        'status' => 'pns',
        'password' => 'password123',
        'is_active' => '1',
    ])->assertRedirect('/pengaturan');

    $target = User::where('email', 'budi@baptwo.test')->first();
    expect($target)->not->toBeNull()
        ->and($target->nama)->toBe('Budi Santoso')
        ->and($target->role)->toBe(UserRole::PENYIDIK)
        ->and($target->created_by)->toBe($admin->id);

    // Update user
    $this->actingAs($admin)->put("/pengaturan/{$target->id}", [
        'nama' => 'Budi Santoso, S.Farm.',
        'nip' => '199505052020011005',
        'email' => 'budi@baptwo.test',
        'role' => UserRole::PIMPINAN->value,
        'status' => 'pppk',
        'password' => '',
        'is_active' => '1',
    ])->assertRedirect('/pengaturan');

    $target->refresh();
    expect($target->nama)->toBe('Budi Santoso, S.Farm.')
        ->and($target->role)->toBe(UserRole::PIMPINAN)
        ->and($target->status)->toBe('pppk')
        ->and($target->updated_by)->toBe($admin->id);

    // Prevent Admin from soft-deleting themselves
    $this->actingAs($admin)->delete("/pengaturan/{$admin->id}")->assertForbidden();

    // Soft delete target user
    $this->actingAs($admin)->delete("/pengaturan/{$target->id}")->assertRedirect('/pengaturan');

    $trashed = User::withTrashed()->find($target->id);
    expect($trashed->trashed())->toBeTrue()
        ->and($trashed->is_active)->toBeFalse();

    // Restore soft-deleted user
    $this->actingAs($admin)->post("/pengaturan/{$target->id}/restore")->assertRedirect('/pengaturan');

    $restored = User::find($target->id);
    expect($restored)->not->toBeNull()
        ->and($restored->trashed())->toBeFalse()
        ->and($restored->is_active)->toBeTrue();
});

it('paginates users by 10 items per page on /pengaturan', function () {
    $admin = User::factory()->administrator()->create(['nama' => 'Admin Utama']);
    User::factory()->count(14)->create();

    $responsePage1 = $this->actingAs($admin)->get('/pengaturan');
    $responsePage1->assertOk();
    expect($responsePage1->viewData('users')->perPage())->toBe(10)
        ->and($responsePage1->viewData('users')->count())->toBe(10)
        ->and($responsePage1->viewData('users')->total())->toBe(15);

    $responsePage2 = $this->actingAs($admin)->get('/pengaturan?page=2');
    $responsePage2->assertOk();
    expect($responsePage2->viewData('users')->count())->toBe(5);
});

it('displays Dihapus status without Soft Delete text and supports searching deleted users via dihapus', function () {
    $admin = User::factory()->administrator()->create(['nama' => 'Admin Pengaturan']);
    $deletedUser = User::factory()->penyidik()->create(['nama' => 'User Dihapus Uji']);
    $activeUser = User::factory()->penyidik()->create(['nama' => 'User Aktif Uji']);

    $deleteResponse = $this->actingAs($admin)->delete("/pengaturan/{$deletedUser->id}");
    $deleteResponse->assertRedirect('/pengaturan')
        ->assertSessionHas('success', fn ($flash) => is_array($flash)
            && ($flash['message'] ?? '') === 'Akun pengguna berhasil dihapus.');

    $indexResponse = $this->actingAs($admin)->get('/pengaturan');
    $indexResponse->assertOk()
        ->assertDontSee('Soft Delete', false)
        ->assertSee('Dihapus')
        ->assertSee('Akun yang dihapus dapat dipulihkan kembali.');

    $searchResponse = $this->actingAs($admin)->get('/pengaturan?search=dihapus');
    $searchResponse->assertOk()
        ->assertSee('User Dihapus Uji')
        ->assertDontSee('User Aktif Uji');
});

it('orders active users first, inactive second, and deleted last across pagination and separates status filters with summary cards', function () {
    $admin = User::factory()->administrator()->create(['nama' => 'Zzz Admin Aktif']);
    $activeUser = User::factory()->penyidik()->create(['nama' => 'Zzz Penyidik Aktif', 'is_active' => true]);
    $inactiveUser = User::factory()->umum()->create(['nama' => 'Aaa Umum Nonaktif', 'is_active' => false]);
    $deletedUser = User::factory()->penyidik()->create(['nama' => 'Aaa Penyidik Dihapus']);
    $deletedUser->delete();

    $response = $this->actingAs($admin)->get('/pengaturan');
    $response->assertOk()
        ->assertSee('Total Pengguna')
        ->assertSee('Status Aktif')
        ->assertSee('Nonaktif')
        ->assertSee('Dihapus: 1')
        ->assertSee('Rincian Peran')
        ->assertSee('fi-rr-user-police')
        ->assertSee('dari total')
        ->assertSee('bulan ini')
        ->assertSeeInOrder(['Zzz Admin Aktif', 'Zzz Penyidik Aktif', 'Aaa Umum Nonaktif', 'Aaa Penyidik Dihapus']);

    // Filter Nonaktif hanya menampilkan user nonaktif (tidak terhapus)
    $this->actingAs($admin)->get('/pengaturan?status=nonaktif')
        ->assertOk()
        ->assertSee('Aaa Umum Nonaktif')
        ->assertDontSee('Zzz Penyidik Aktif')
        ->assertDontSee('Aaa Penyidik Dihapus');

    // Filter Dihapus hanya menampilkan user terhapus
    $this->actingAs($admin)->get('/pengaturan?status=dihapus')
        ->assertOk()
        ->assertSee('Aaa Penyidik Dihapus')
        ->assertDontSee('Aaa Umum Nonaktif')
        ->assertDontSee('Zzz Penyidik Aktif');
});
