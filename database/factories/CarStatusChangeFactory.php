<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarStatus;
use App\Models\CarStatusChange;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarStatusChange>
 */
class CarStatusChangeFactory extends Factory
{
    /**
     * Define the model's default state: a user putting an available car on hold.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'user_id' => User::factory(),
            'from_status' => CarStatus::Available,
            'to_status' => CarStatus::OnHold,
        ];
    }
}
