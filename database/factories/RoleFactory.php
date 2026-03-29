<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            // Keep names simple and unique to satisfy the unique constraint.
            'name' => $this->faker->unique()->randomElement([
                'intern', 'mentor', 'admin', 'hr', 'manager',
            ]),
        ];
    }

    public function intern(): static
    {
        return $this->state(fn () => ['name' => 'intern']);
    }

    public function mentor(): static
    {
        return $this->state(fn () => ['name' => 'mentor']);
    }
}
