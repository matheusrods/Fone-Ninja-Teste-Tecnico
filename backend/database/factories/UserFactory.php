<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => Role::Admin,
        ];
    }

    public function vendedor(): static
    {
        return $this->state(['role' => Role::Vendedor]);
    }

    public function comprador(): static
    {
        return $this->state(['role' => Role::Comprador]);
    }
}
