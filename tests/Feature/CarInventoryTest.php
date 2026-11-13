<?php

use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(LazilyRefreshDatabase::class);

it('redirects guests to the login page', function (string $routeName) {
    $car = Car::factory()->create();

    $this->get(route($routeName, $car))->assertRedirect(route('login'));
})->with([
    'car list' => 'cars.index',
    'car detail' => 'cars.show',
]);

describe('index', function () {
    it('lists cars newest first, twelve per page', function () {
        $cars = Car::factory()
            ->count(13)
            ->sequence(fn (Sequence $sequence) => ['created_at' => now()->subDays($sequence->index)])
            ->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cars/Index')
                ->has('cars.data', 12)
                ->where('cars.data.0.id', $cars->first()->id)
                ->where('cars.data.11.id', $cars[11]->id)
                ->where('cars.total', 13)
                ->where('cars.last_page', 2)
            );
    });

    it('shows the remaining oldest cars on the next page', function () {
        $cars = Car::factory()
            ->count(13)
            ->sequence(fn (Sequence $sequence) => ['created_at' => now()->subDays($sequence->index)])
            ->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cars/Index')
                ->has('cars.data', 1)
                ->where('cars.data.0.id', $cars->last()->id)
                ->where('cars.current_page', 2)
            );
    });

    it('only lists cars whose make, model or trim match the search', function () {
        $civic = Car::factory()->create(['make' => 'Honda', 'model' => 'Civic', 'trim' => 'Sport']);
        Car::factory()->create(['make' => 'Ford', 'model' => 'F-150', 'trim' => 'XLT']);

        $this->actingAs(User::factory()->create())
            ->get(route('cars.index', ['search' => 'civic']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cars/Index')
                ->has('cars.data', 1)
                ->where('cars.data.0.id', $civic->id)
                ->where('filters.search', 'civic')
            );
    });
});

describe('show', function () {
    it('shows one car with its photo credit', function () {
        $car = Car::factory()->create();
        Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cars/Show')
                ->has('car', fn (Assert $page) => $page
                    ->where('id', $car->id)
                    ->where('make', $car->make)
                    ->where('model', $car->model)
                    ->where('photo_url', $car->photo_url)
                    ->where('photo_author', $car->photo_author)
                    ->where('photo_licence', $car->photo_licence)
                    ->etc()
                )
            );
    });

    it('returns 404 for an unknown car', function () {
        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', 999999))
            ->assertNotFound();
    });
});
