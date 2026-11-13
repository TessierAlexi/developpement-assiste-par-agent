<?php

use App\Models\Car;
use App\Models\CarInventoryService;
use App\Models\CarStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('adds a new car to the inventory as available', function () {
    $car = app(CarInventoryService::class)->add([
        ...Car::factory()->raw(),
        'stock_number' => 'STK-1001',
        'received_on' => '2026-11-02',
    ]);

    expect($car->refresh())
        ->stock_number->toBe('STK-1001')
        ->received_on->toDateString()->toBe('2026-11-02')
        ->status->toBe(CarStatus::Available)
        ->sold_on->toBeNull();
});

it('records today as the sale date when a car is sold', function () {
    $this->travelTo('2026-11-10 15:30:00');
    $car = Car::factory()->onHold()->create();

    app(CarInventoryService::class)->changeStatus($car, CarStatus::Sold);

    expect($car->refresh())
        ->status->toBe(CarStatus::Sold)
        ->sold_on->toDateString()->toBe('2026-11-10');
});

it('keeps the original sale date when a sold car is marked sold again', function () {
    $this->travelTo('2026-11-10');
    $car = Car::factory()->sold('2026-10-01')->create();

    app(CarInventoryService::class)->changeStatus($car, CarStatus::Sold);

    expect($car->refresh()->sold_on->toDateString())->toBe('2026-10-01');
});

it('clears the sale date when a sold car goes back on the lot', function (CarStatus $status) {
    $car = Car::factory()->sold('2026-10-01')->create();

    app(CarInventoryService::class)->changeStatus($car, $status);

    expect($car->refresh())
        ->status->toBe($status)
        ->sold_on->toBeNull();
})->with([
    'available' => fn () => CarStatus::Available,
    'on hold' => fn () => CarStatus::OnHold,
]);

it('updates the details of a sold car without touching its status or sale date', function () {
    $car = Car::factory()->sold('2026-10-01')->create(['price' => 30_000]);

    app(CarInventoryService::class)->update($car, ['price' => 28_500]);

    expect($car->refresh())
        ->price->toBe(28_500)
        ->status->toBe(CarStatus::Sold)
        ->sold_on->toDateString()->toBe('2026-10-01');
});

it('ignores status and sale date passed with the car details', function () {
    $car = Car::factory()->create();

    app(CarInventoryService::class)->update($car, [
        'status' => CarStatus::Sold->value,
        'sold_on' => '2026-10-01',
    ]);

    expect($car->refresh())
        ->status->toBe(CarStatus::Available)
        ->sold_on->toBeNull();
});

it('removes a car from the inventory', function () {
    $car = Car::factory()->create();

    app(CarInventoryService::class)->remove($car);

    $this->assertModelMissing($car);
});
