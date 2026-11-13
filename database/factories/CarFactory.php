<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    /**
     * Make, model, trim and body type combinations that exist in real life.
     *
     * @var list<array{0: string, 1: string, 2: string, 3: string}>
     */
    protected const VEHICLES = [
        ['Toyota', 'Corolla', 'LE', 'Sedan'],
        ['Honda', 'Civic', 'Sport', 'Sedan'],
        ['Mazda', 'CX-5', 'GS', 'SUV'],
        ['Ford', 'F-150', 'XLT', 'Pickup'],
        ['Subaru', 'Outback', 'Touring', 'Wagon'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$make, $model, $trim, $bodyType] = fake()->randomElement(self::VEHICLES);

        return [
            'stock_number' => fake()->unique()->bothify('STK-#####'),
            'status' => CarStatus::Available,
            'received_on' => fake()->dateTimeBetween('-120 days', '-1 day')->format('Y-m-d'),
            'sold_on' => null,
            'year' => fake()->numberBetween(2015, 2026),
            'make' => $make,
            'model' => $model,
            'trim' => $trim,
            'body_type' => $bodyType,
            'fuel_type' => fake()->randomElement(['Gasoline', 'Hybrid']),
            'transmission' => fake()->randomElement(['Automatic', 'Manual']),
            'colour' => fake()->randomElement(['White', 'Black', 'Silver', 'Blue', 'Red']),
            'mileage_km' => fake()->numberBetween(0, 180_000),
            'price' => fake()->numberBetween(80, 600) * 100,
            'description' => fake()->sentence(12),
            'photo_url' => 'https://upload.wikimedia.org/wikipedia/commons/'.fake()->uuid().'.jpg',
            'photo_source_url' => 'https://commons.wikimedia.org/wiki/File:'.fake()->uuid().'.jpg',
            'photo_author' => fake()->name(),
            'photo_licence' => 'CC BY-SA 4.0',
            'photo_licence_url' => 'https://creativecommons.org/licenses/by-sa/4.0',
            'photo_attribution_required' => true,
        ];
    }

    /**
     * Indicate that the car has no photo.
     */
    public function withoutPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo_url' => null,
            'photo_source_url' => null,
            'photo_author' => null,
            'photo_licence' => null,
            'photo_licence_url' => null,
            'photo_attribution_required' => false,
        ]);
    }

    /**
     * Indicate that the car has no price, colour, description or photo yet.
     */
    public function withMissingDetails(): static
    {
        return $this->withoutPhoto()->state(fn (array $attributes) => [
            'price' => null,
            'colour' => null,
            'description' => null,
        ]);
    }

    /**
     * Indicate that a customer has put the car on hold.
     */
    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CarStatus::OnHold,
            'sold_on' => null,
        ]);
    }

    /**
     * Indicate that the car was sold, on the given date or yesterday.
     */
    public function sold(?string $soldOn = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CarStatus::Sold,
            'sold_on' => $soldOn ?? now()->subDay()->toDateString(),
        ]);
    }
}
