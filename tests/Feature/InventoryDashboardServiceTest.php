<?php

use App\Models\Car;
use App\Services\InventoryDashboardService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->travelTo('2026-11-10 15:30:00');
});

it('reports the share of unsold cars that are available, leaving sold cars out', function () {
    Car::factory()->count(3)->create();
    Car::factory()->onHold()->create();
    Car::factory()->count(2)->sold()->create();

    expect(app(InventoryDashboardService::class)->availableShare())->toBe(75);
});

it('averages the days unsold cars have spent in inventory', function () {
    Car::factory()->create(['received_on' => '2026-11-10']);
    Car::factory()->create(['received_on' => '2026-10-31']);
    Car::factory()->onHold()->create(['received_on' => '2026-10-06']);
    Car::factory()->sold()->create(['received_on' => '2025-01-01']);

    expect(app(InventoryDashboardService::class)->averageDaysInInventory())->toBe(15);
});

it('counts the cars sold during the current month only', function () {
    Car::factory()->sold('2026-11-01')->create();
    Car::factory()->sold('2026-11-10')->create();
    Car::factory()->sold('2026-10-31')->create();
    Car::factory()->sold('2025-11-05')->create();
    Car::factory()->create();

    expect(app(InventoryDashboardService::class)->salesThisMonth())->toBe(2);
});

it('totals the asking prices of available cars, skipping cars without a price', function () {
    Car::factory()->create(['price' => 20_000]);
    Car::factory()->create(['price' => 15_500]);
    Car::factory()->create(['price' => null]);
    Car::factory()->onHold()->create(['price' => 40_000]);
    Car::factory()->sold()->create(['price' => 30_000]);

    expect(app(InventoryDashboardService::class)->availableInventoryValue())->toBe(35_500);
});

it('counts the cars in every status, including statuses with no car', function () {
    Car::factory()->count(2)->create();
    Car::factory()->sold()->create();

    expect(app(InventoryDashboardService::class)->countsByStatus())->toBe([
        ['value' => 'available', 'label' => 'Available', 'count' => 2],
        ['value' => 'on_hold', 'label' => 'On hold', 'count' => 0],
        ['value' => 'sold', 'label' => 'Sold', 'count' => 1],
    ]);
});

it('averages the margin of sold cars, including cars sold at a loss', function () {
    Car::factory()->sold()->create(['price' => 20_000, 'cost_price' => 15_000]);
    Car::factory()->sold()->create(['price' => 10_000, 'cost_price' => 12_000]);
    Car::factory()->create(['price' => 90_000, 'cost_price' => 10_000]);

    expect(app(InventoryDashboardService::class)->averageSoldMargin())->toBe(1_500);
});

it('ignores sold cars missing an asking or cost price when averaging the margin', function () {
    Car::factory()->sold()->create(['price' => 20_000, 'cost_price' => 16_000]);
    Car::factory()->sold()->create(['price' => null, 'cost_price' => 10_000]);
    Car::factory()->sold()->create(['price' => 50_000, 'cost_price' => null]);

    expect(app(InventoryDashboardService::class)->averageSoldMargin())->toBe(4_000);
});

it('has no average margin when no sold car has both prices', function () {
    Car::factory()->sold()->create(['price' => null, 'cost_price' => null]);

    expect(app(InventoryDashboardService::class)->averageSoldMargin())->toBeNull();
});

it('lists the five unsold cars received the longest ago, oldest first', function () {
    $newest = Car::factory()->create(['received_on' => '2026-11-09']);
    $oldestCars = collect(['2026-06-01', '2026-07-01', '2026-08-01', '2026-09-01', '2026-10-01'])
        ->map(fn (string $receivedOn) => Car::factory()->create(['received_on' => $receivedOn]));
    Car::factory()->sold()->create(['received_on' => '2024-01-01']);

    $listed = app(InventoryDashboardService::class)->oldestCars();

    expect($listed)->toHaveCount(5)
        ->and(array_column($listed, 'id'))->toBe($oldestCars->pluck('id')->all())
        ->not->toContain($newest->id);
});

it('describes each of the oldest cars with its days in inventory', function () {
    $car = Car::factory()->onHold()->create([
        'stock_number' => 'STK-0042',
        'year' => 2019,
        'make' => 'Mazda',
        'model' => 'CX-5',
        'received_on' => '2026-10-11',
        'photo_url' => 'https://upload.wikimedia.org/cx5.jpg',
    ]);

    expect(app(InventoryDashboardService::class)->oldestCars())->toBe([[
        'id' => $car->id,
        'stock_number' => 'STK-0042',
        'status' => 'on_hold',
        'year' => 2019,
        'make' => 'Mazda',
        'model' => 'CX-5',
        'photo_url' => 'https://upload.wikimedia.org/cx5.jpg',
        'received_on' => '2026-10-11',
        'days_in_inventory' => 30,
    ]]);
});

it('summarises an empty inventory without failing', function () {
    expect(app(InventoryDashboardService::class)->summary())->toBe([
        'gauges' => [
            'availableShare' => ['value' => null, 'target' => 100],
            'averageDaysInInventory' => ['value' => null, 'target' => 60],
            'salesThisMonth' => ['value' => 0, 'target' => 10],
        ],
        'tiles' => [
            'availableInventoryValue' => 0,
            'countsByStatus' => [
                ['value' => 'available', 'label' => 'Available', 'count' => 0],
                ['value' => 'on_hold', 'label' => 'On hold', 'count' => 0],
                ['value' => 'sold', 'label' => 'Sold', 'count' => 0],
            ],
            'averageSoldMargin' => null,
        ],
        'oldestCars' => [],
    ]);
});

it('has no available share or average age when every car is sold', function () {
    Car::factory()->sold()->create();

    expect(app(InventoryDashboardService::class))
        ->availableShare()->toBeNull()
        ->averageDaysInInventory()->toBeNull();
});
