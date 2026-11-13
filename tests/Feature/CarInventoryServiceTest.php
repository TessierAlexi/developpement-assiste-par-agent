<?php

use App\Enums\CarStatus;
use App\Models\Car;
use App\Models\User;
use App\Services\CarInventoryService;
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

it('records the old status, the new status, the user and the time when the status changes', function () {
    $this->travelTo('2026-11-10 15:30:00');
    $car = Car::factory()->create();
    $user = User::factory()->create();

    app(CarInventoryService::class)->changeStatus($car, CarStatus::OnHold, $user);

    expect($car->statusChanges()->sole())
        ->from_status->toBe(CarStatus::Available)
        ->to_status->toBe(CarStatus::OnHold)
        ->user_id->toBe($user->id)
        ->created_at->toDateTimeString()->toBe('2026-11-10 15:30:00');
});

it('records a status change made while editing the car details', function () {
    $car = Car::factory()->onHold()->create();
    $user = User::factory()->create();

    app(CarInventoryService::class)->update($car, ['price' => 28_500], CarStatus::Sold, $user);

    expect($car->statusChanges()->sole())
        ->from_status->toBe(CarStatus::OnHold)
        ->to_status->toBe(CarStatus::Sold)
        ->user_id->toBe($user->id);
});

it('does not record a status change when the status stays the same', function () {
    $car = Car::factory()->onHold()->create();
    $user = User::factory()->create();

    app(CarInventoryService::class)->changeStatus($car, CarStatus::OnHold, $user);
    app(CarInventoryService::class)->update($car, ['price' => 28_500], null, $user);

    expect($car->statusChanges()->count())->toBe(0);
});

it('does not record a status change for a car that was just added', function () {
    $car = app(CarInventoryService::class)->add(Car::factory()->raw(), CarStatus::OnHold);

    expect($car->refresh()->status)->toBe(CarStatus::OnHold)
        ->and($car->statusChanges()->count())->toBe(0);
});
