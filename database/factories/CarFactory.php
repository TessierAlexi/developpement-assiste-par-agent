<?php

namespace Database\Factories;

use App\Enums\CarStatus;
use App\Enums\Drivetrain;
use App\Models\Car;
use App\Support\Vin;
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
     * Define the model's default state. The VIN is made up, yet valid: 17 allowed
     * characters with the right check digit.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$make, $model, $trim, $bodyType] = fake()->randomElement(self::VEHICLES);

        return [
            'stock_number' => fake()->unique()->bothify('STK-#####'),
            'vin' => Vin::withCheckDigit(fake()->unique()->regexify('[A-HJ-NPR-Z0-9]{'.Vin::LENGTH.'}')),
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
            'interior_colour' => null,
            'engine' => null,
            'engine_displacement_l' => null,
            'cylinders' => null,
            'drivetrain' => null,
            'doors' => null,
            'seats' => null,
            'city_l_per_100km' => null,
            'highway_l_per_100km' => null,
            'mileage_km' => fake()->numberBetween(0, 180_000),
            'price' => fake()->numberBetween(80, 600) * 100,
            'cost_price' => null,
            'description' => fake()->sentence(12),
            'internal_notes' => null,
            'photo_url' => 'https://upload.wikimedia.org/wikipedia/commons/'.fake()->uuid().'.jpg',
            'photo_source_url' => 'https://commons.wikimedia.org/wiki/File:'.fake()->uuid().'.jpg',
            'photo_author' => fake()->name(),
            'photo_licence' => 'CC BY-SA 4.0',
            'photo_licence_url' => 'https://creativecommons.org/licenses/by-sa/4.0',
            'photo_attribution_required' => true,
        ];
    }

    /**
     * Indicate that the technical details of the car have been entered.
     */
    public function withTechnicalDetails(): static
    {
        return $this->state(fn (array $attributes) => [
            'interior_colour' => fake()->randomElement(['Black cloth', 'Grey cloth', 'Black leather']),
            'engine' => '2.0L I4',
            'engine_displacement_l' => 2.0,
            'cylinders' => 4,
            'drivetrain' => fake()->randomElement(Drivetrain::cases()),
            'doors' => 4,
            'seats' => 5,
            'city_l_per_100km' => fake()->randomFloat(1, 7, 12),
            'highway_l_per_100km' => fake()->randomFloat(1, 5, 9),
            'cost_price' => fake()->numberBetween(60, 400) * 100,
            'internal_notes' => fake()->sentence(8),
        ]);
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
