<?php

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(LazilyRefreshDatabase::class);

it('redirects guests to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('shows the inventory gauges, tiles and oldest cars', function () {
    $this->travelTo('2026-11-10 15:30:00');
    $oldest = Car::factory()->create(['received_on' => '2026-09-11', 'price' => 20_000]);
    Car::factory()->onHold()->create(['received_on' => '2026-11-10', 'price' => 30_000]);
    Car::factory()->sold('2026-11-03')->create(['price' => 25_000, 'cost_price' => 21_000]);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('gauges.availableShare', ['value' => 50, 'target' => 100])
            ->where('gauges.averageDaysInInventory', ['value' => 30, 'target' => 60])
            ->where('gauges.salesThisMonth', ['value' => 1, 'target' => 10])
            ->where('tiles.availableInventoryValue', 20_000)
            ->where('tiles.countsByStatus.0', ['value' => 'available', 'label' => 'Available', 'count' => 1])
            ->where('tiles.countsByStatus.1.count', 1)
            ->where('tiles.countsByStatus.2.count', 1)
            ->where('tiles.averageSoldMargin', 4_000)
            ->has('oldestCars', 2)
            ->where('oldestCars.0.id', $oldest->id)
            ->where('oldestCars.0.days_in_inventory', 60)
        );
});

it('shows the dashboard when the inventory is empty', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('gauges.availableShare.value', null)
            ->where('gauges.salesThisMonth.value', 0)
            ->where('tiles.averageSoldMargin', null)
            ->has('oldestCars', 0)
        );
});
