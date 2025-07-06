<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('the user can login ', function () {
    $user = User::factory()->create([
        'email' => 'email@email.com',
        'password' => Hash::make('password')
    ]);
    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'password'
    ]);

    $response->assertStatus(200);
});
