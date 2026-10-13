<?php

use App\Models\User;

test('the application redirects guests to signin and allows authenticated users', function () {
    $this->get('/')->assertRedirect('/signin');

    $user = User::factory()->make();

    $this->actingAs($user)->get('/')->assertStatus(200);
});
