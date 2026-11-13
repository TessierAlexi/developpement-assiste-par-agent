<?php

use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

it('returns 401 to guests without asking NHTSA anything', function () {
    Http::preventStrayRequests();
    Http::fake(['api.nhtsa.gov/recalls/recallsByVehicle*' => Http::response(nhtsaRecalls([nhtsaRecall()]))]);
    $car = Car::factory()->create();

    $this->getJson(route('cars.recalls', $car))
        ->assertUnauthorized();

    Http::assertNothingSent();
});

it('returns the safety recalls NHTSA lists for the make, model and year of the car', function () {
    Http::preventStrayRequests();
    Http::fake(['api.nhtsa.gov/recalls/recallsByVehicle*' => Http::response(nhtsaRecalls([nhtsaRecall()]))]);
    $car = Car::factory()->create(['make' => 'Honda', 'model' => 'Accord', 'year' => 2023]);

    $this->actingAs(User::factory()->create())
        ->getJson(route('cars.recalls', $car))
        ->assertOk()
        ->assertExactJson([
            'recalls' => [[
                'campaign_number' => '23V782000',
                'component' => 'SEAT BELTS:FRONT',
                'summary' => 'Honda is recalling certain 2023-2024 Accord and HR-V vehicles.',
                'consequence' => 'A seat belt pretensioner missing a rivet will not properly restrain the occupant.',
                'remedy' => 'Dealers will inspect and replace the seat belt pretensioner assemblies, free of charge.',
            ]],
        ]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.nhtsa.gov/recalls/recallsByVehicle?make=Honda&model=Accord&modelYear=2023');
});

it('returns an empty list when NHTSA lists no recall for the car', function () {
    Http::preventStrayRequests();
    Http::fake(['api.nhtsa.gov/recalls/recallsByVehicle*' => Http::response(nhtsaRecalls([]))]);
    $car = Car::factory()->create();

    $this->actingAs(User::factory()->create())
        ->getJson(route('cars.recalls', $car))
        ->assertOk()
        ->assertExactJson(['recalls' => []]);
});

it('returns 503 with a message when NHTSA does not answer', function () {
    Http::preventStrayRequests();
    Http::fake(['api.nhtsa.gov/recalls/recallsByVehicle*' => Http::failedConnection()]);
    $car = Car::factory()->create();

    $this->actingAs(User::factory()->create())
        ->getJson(route('cars.recalls', $car))
        ->assertServiceUnavailable()
        ->assertExactJson(['message' => 'The NHTSA recall service is unavailable right now. Try again later.']);
});

it('returns 404 for a car that is not in the inventory', function () {
    Http::preventStrayRequests();
    Http::fake(['api.nhtsa.gov/recalls/recallsByVehicle*' => Http::response(nhtsaRecalls([nhtsaRecall()]))]);

    $this->actingAs(User::factory()->create())
        ->getJson(route('cars.recalls', ['car' => 999]))
        ->assertNotFound();

    Http::assertNothingSent();
});

it('returns 429 once a user goes over 30 recall checks in a minute', function () {
    Http::preventStrayRequests();
    Http::fake(['api.nhtsa.gov/recalls/recallsByVehicle*' => Http::response(nhtsaRecalls([nhtsaRecall()]))]);
    $car = Car::factory()->create();
    $user = User::factory()->create();

    foreach (range(1, 30) as $check) {
        $this->actingAs($user)
            ->getJson(route('cars.recalls', $car))
            ->assertOk();
    }

    $this->actingAs($user)
        ->getJson(route('cars.recalls', $car))
        ->assertTooManyRequests()
        ->assertHeader('Retry-After')
        ->assertExactJson(['message' => 'Too many recall checks. Wait a minute and try again.']);
});

it('counts the recall checks of each user separately', function () {
    Http::preventStrayRequests();
    Http::fake(['api.nhtsa.gov/recalls/recallsByVehicle*' => Http::response(nhtsaRecalls([nhtsaRecall()]))]);
    $car = Car::factory()->create();
    $userOverTheLimit = User::factory()->create();

    foreach (range(1, 31) as $check) {
        $this->actingAs($userOverTheLimit)->getJson(route('cars.recalls', $car));
    }

    $this->actingAs(User::factory()->create())
        ->getJson(route('cars.recalls', $car))
        ->assertOk();
});

it('lets a user over the limit check recalls again a minute later', function () {
    Http::preventStrayRequests();
    Http::fake(['api.nhtsa.gov/recalls/recallsByVehicle*' => Http::response(nhtsaRecalls([nhtsaRecall()]))]);
    $car = Car::factory()->create();
    $user = User::factory()->create();

    foreach (range(1, 31) as $check) {
        $this->actingAs($user)->getJson(route('cars.recalls', $car));
    }

    $this->travel(61)->seconds();

    $this->actingAs($user)
        ->getJson(route('cars.recalls', $car))
        ->assertOk();
});
