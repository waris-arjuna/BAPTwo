<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role' => UserRole::PENYIDIK,
            'nama' => fake()->name(),
            'nip' => fake()->unique()->numerify('19################'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'tempat_lahir' => 'Banjarmasin',
            'tanggal_lahir' => '1990-05-15',
            'jenis_kelamin' => 'laki-laki',
            'status_pernikahan' => 'kawin',
            'no_telp' => '081251234567',
            'status' => 'pns',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMINISTRATOR,
        ]);
    }

    public function pimpinan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::UMUM,
        ]);
    }

    public function umum(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::UMUM,
        ]);
    }

    public function penyidik(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::PENYIDIK,
        ]);
    }
}
