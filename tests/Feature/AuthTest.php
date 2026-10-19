<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\MathCaptchaService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds three dev accounts with correct roles on singular user table', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(3);

    $admin = User::where('email', 'admin@baptwo.test')->first();
    $pimpinan = User::where('email', 'pimpinan@baptwo.test')->first();
    $penyidik = User::where('email', 'penyidik@baptwo.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->role)->toBe(UserRole::ADMINISTRATOR)
        ->and($admin->isAdmin())->toBeTrue()
        ->and($pimpinan->role)->toBe(UserRole::PIMPINAN)
        ->and($pimpinan->isPimpinan())->toBeTrue()
        ->and($penyidik->role)->toBe(UserRole::PENYIDIK)
        ->and($penyidik->isPenyidik())->toBeTrue();
});

it('authenticates active user with valid credentials and math captcha', function () {
    $user = User::factory()->penyidik()->create([
        'email' => 'penyidik@baptwo.test',
        'password' => 'password',
    ]);

    app(MathCaptchaService::class)->generate();
    $answer = session(MathCaptchaService::SESSION_ANSWER_KEY);

    $response = $this->post('/signin', [
        'email' => 'penyidik@baptwo.test',
        'password' => 'password',
        'captcha' => (string) $answer,
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('login_success');
    $this->assertAuthenticatedAs($user);
});

it('rejects login with specific error when password is wrong', function () {
    User::factory()->create([
        'email' => 'penyidik@baptwo.test',
        'password' => 'password',
    ]);

    app(MathCaptchaService::class)->generate();
    $answer = session(MathCaptchaService::SESSION_ANSWER_KEY);

    $response = $this->from('/signin')->post('/signin', [
        'email' => 'penyidik@baptwo.test',
        'password' => 'salah-password',
        'captcha' => (string) $answer,
    ]);

    $response->assertRedirect('/signin');
    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

it('rejects login with specific error when email is unregistered or captcha is wrong', function () {
    app(MathCaptchaService::class)->generate();
    $answer = session(MathCaptchaService::SESSION_ANSWER_KEY);

    $unregistered = $this->from('/signin')->post('/signin', [
        'email' => 'tidakada@baptwo.test',
        'password' => 'password',
        'captcha' => (string) $answer,
    ]);

    $unregistered->assertRedirect('/signin');
    $unregistered->assertSessionHasErrors('email');

    app(MathCaptchaService::class)->generate();
    $wrongCaptcha = $this->from('/signin')->post('/signin', [
        'email' => 'tidakada@baptwo.test',
        'password' => 'password',
        'captcha' => '99999',
    ]);

    $wrongCaptcha->assertRedirect('/signin');
    $wrongCaptcha->assertSessionHasErrors('captcha');
});

it('rejects login when user is inactive', function () {
    User::factory()->create([
        'email' => 'nonaktif@baptwo.test',
        'password' => 'password',
        'is_active' => false,
    ]);

    app(MathCaptchaService::class)->generate();
    $answer = session(MathCaptchaService::SESSION_ANSWER_KEY);

    $response = $this->from('/signin')->post('/signin', [
        'email' => 'nonaktif@baptwo.test',
        'password' => 'password',
        'captcha' => (string) $answer,
    ]);

    $response->assertRedirect('/signin');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('logs out authenticated user and invalidates session', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/masuk');
    $this->assertGuest();
});

it('renders dynamic user profile and popover menu items in sidebar widget', function () {
    $user = User::factory()->penyidik()->create([
        'nama' => 'Waris Arjuna',
    ]);

    $response = $this->actingAs($user)->get('/');

    $response->assertOk()
        ->assertSee('Waris Arjuna')
        ->assertSee('WA')
        ->assertSee(__('Settings'))
        ->assertSee(__('Profile'))
        ->assertSee(__('Sign Out'))
        ->assertSee(route('logout'));
});
