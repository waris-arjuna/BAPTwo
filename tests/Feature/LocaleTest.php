<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('locks locale to id and dir to ltr', function () {
    $user = User::factory()->make();

    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
    $response->assertSee('dir="ltr"', false);
    $response->assertDontSee('Language', false);
    expect(app()->getLocale())->toBe('id');
});

it('does not have legacy locale switch route', function () {
    $response = $this->get('/locale/en');

    $response->assertNotFound();
});
