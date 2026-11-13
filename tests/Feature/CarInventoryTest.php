<?php

use App\Models\Car;
use App\Models\CarStatus;
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
    'new car form' => 'cars.create',
    'edit car form' => 'cars.edit',
]);

it('redirects guests to the login page without changing the inventory', function (string $method, string $routeName) {
    $car = Car::factory()->create();

    $this->{$method}(route($routeName, $car), ['status' => CarStatus::Sold->value])
        ->assertRedirect(route('login'));

    $this->assertModelExists($car);
    expect($car->refresh()->status)->toBe(CarStatus::Available);
})->with([
    'add' => ['post', 'cars.store'],
    'update' => ['put', 'cars.update'],
    'delete' => ['delete', 'cars.destroy'],
    'change status' => ['patch', 'cars.status.update'],
]);

/**
 * A complete, valid set of car details as the add and edit forms send them.
 *
 * @return array<string, mixed>
 */
function carFormPayload(array $overrides = []): array
{
    return [
        'stock_number' => 'STK-2042',
        'received_on' => '2026-11-03',
        'year' => 2023,
        'make' => 'Honda',
        'model' => 'Civic',
        'trim' => 'Sport',
        'body_type' => 'Sedan',
        'fuel_type' => 'Gasoline',
        'transmission' => 'Manual',
        'colour' => 'Sonic Grey Pearl',
        'mileage_km' => 35_600,
        'price' => 32_400,
        'description' => 'One owner, winter tires included.',
        'photo_url' => null,
        'photo_source_url' => null,
        'photo_author' => null,
        'photo_licence' => null,
        'photo_licence_url' => null,
        'photo_attribution_required' => false,
        ...$overrides,
    ];
}

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

    it('shows the status and stock number of each car', function () {
        Car::factory()->onHold()->create(['stock_number' => 'STK-0007']);

        $this->actingAs(User::factory()->create())
            ->get(route('cars.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('cars.data.0.status', CarStatus::OnHold->value)
                ->where('cars.data.0.stock_number', 'STK-0007')
                ->where('statuses', [
                    ['value' => 'available', 'label' => 'Available'],
                    ['value' => 'on_hold', 'label' => 'On hold'],
                    ['value' => 'sold', 'label' => 'Sold'],
                ])
            );
    });

    it('only lists cars with the requested status, combined with the search', function () {
        $soldCivic = Car::factory()->sold()->create(['make' => 'Honda', 'model' => 'Civic']);
        Car::factory()->create(['make' => 'Honda', 'model' => 'Civic']);
        Car::factory()->sold()->create(['make' => 'Ford', 'model' => 'F-150', 'trim' => 'XLT']);

        $this->actingAs(User::factory()->create())
            ->get(route('cars.index', ['search' => 'civic', 'status' => 'sold']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('cars.data', 1)
                ->where('cars.data.0.id', $soldCivic->id)
                ->where('filters.status', 'sold')
            );
    });

    it('lists every car when the status filter is unknown', function () {
        Car::factory()->create();
        Car::factory()->sold()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.index', ['status' => 'stolen']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('cars.data', 2)
                ->where('filters.status', null)
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

    it('shows the status, stock number and inventory dates', function () {
        $car = Car::factory()->sold('2026-11-05')->create([
            'stock_number' => 'STK-0042',
            'received_on' => '2026-09-14',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('car.status', 'sold')
                ->where('car.stock_number', 'STK-0042')
                ->where('car.received_on', '2026-09-14')
                ->where('car.sold_on', '2026-11-05')
                ->has('statuses', 3)
            );
    });

    it('returns 404 for an unknown car', function () {
        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', 999999))
            ->assertNotFound();
    });
});

describe('create', function () {
    it('renders the new car form with today as the received date', function () {
        $this->travelTo('2026-11-10 09:00:00');

        $this->actingAs(User::factory()->create())
            ->get(route('cars.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cars/Create')
                ->where('defaults.received_on', '2026-11-10')
            );
    });
});

describe('store', function () {
    it('adds the car as available and shows it', function () {
        $this->travelTo('2026-11-10');

        $response = $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), carFormPayload());

        $car = Car::sole();
        $response->assertRedirect(route('cars.show', $car));
        expect($car)
            ->stock_number->toBe('STK-2042')
            ->received_on->toDateString()->toBe('2026-11-03')
            ->make->toBe('Honda')
            ->price->toBe(32_400)
            ->status->toBe(CarStatus::Available)
            ->sold_on->toBeNull();
    });

    it('rejects a car with missing details', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), [])
            ->assertSessionHasErrors([
                'stock_number' => 'The stock number field is required.',
                'received_on', 'year', 'make', 'model', 'trim', 'body_type',
                'fuel_type', 'transmission', 'colour', 'mileage_km', 'price',
            ]);

        expect(Car::count())->toBe(0);
    });

    it('rejects a stock number already used by another car', function () {
        Car::factory()->create(['stock_number' => 'STK-2042']);

        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), carFormPayload(['stock_number' => 'STK-2042']))
            ->assertSessionHasErrors(['stock_number' => 'The stock number has already been taken.']);

        expect(Car::count())->toBe(1);
    });

    it('rejects invalid car details', function (string $field, mixed $value, string $message) {
        $this->travelTo('2026-11-10');

        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), carFormPayload([$field => $value]))
            ->assertSessionHasErrors([$field => $message]);

        expect(Car::count())->toBe(0);
    })->with([
        'received in the future' => ['received_on', '2026-11-11', 'The received on field must be a date before or equal to today.'],
        'negative mileage' => ['mileage_km', -1, 'The mileage km field must be at least 0.'],
        'negative price' => ['price', -1, 'The price field must be at least 0.'],
        'photo that is not a URL' => ['photo_url', 'not a url', 'The photo url field must be a valid URL.'],
    ]);
});

describe('edit', function () {
    it('renders the edit form with the car', function () {
        $car = Car::factory()->create(['received_on' => '2026-09-14']);

        $this->actingAs(User::factory()->create())
            ->get(route('cars.edit', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cars/Edit')
                ->where('car.id', $car->id)
                ->where('car.received_on', '2026-09-14')
            );
    });
});

describe('update', function () {
    it('saves the new details and keeps its own stock number', function () {
        $car = Car::factory()->create(['stock_number' => 'STK-2042']);

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['price' => 29_900]))
            ->assertRedirect(route('cars.show', $car));

        expect($car->refresh())
            ->stock_number->toBe('STK-2042')
            ->price->toBe(29_900)
            ->model->toBe('Civic');
    });

    it('does not change the status through the edit form', function () {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['status' => 'sold', 'sold_on' => '2026-11-01']))
            ->assertRedirect(route('cars.show', $car));

        expect($car->refresh())
            ->status->toBe(CarStatus::Available)
            ->sold_on->toBeNull();
    });

    it('rejects a stock number already used by another car', function () {
        Car::factory()->create(['stock_number' => 'STK-0001']);
        $car = Car::factory()->create(['stock_number' => 'STK-2042']);

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['stock_number' => 'STK-0001']))
            ->assertSessionHasErrors(['stock_number' => 'The stock number has already been taken.']);

        expect($car->refresh()->stock_number)->toBe('STK-2042');
    });
});

describe('destroy', function () {
    it('deletes the car and goes back to the list', function () {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('cars.destroy', $car))
            ->assertRedirect(route('cars.index'));

        $this->assertModelMissing($car);
    });
});

describe('status', function () {
    it('marks the car sold today and shows it again', function () {
        $this->travelTo('2026-11-10');
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->from(route('cars.show', $car))
            ->patch(route('cars.status.update', $car), ['status' => 'sold'])
            ->assertRedirect(route('cars.show', $car));

        expect($car->refresh())
            ->status->toBe(CarStatus::Sold)
            ->sold_on->toDateString()->toBe('2026-11-10');
    });

    it('rejects an unknown status', function () {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('cars.status.update', $car), ['status' => 'stolen'])
            ->assertSessionHasErrors(['status' => 'The selected status is invalid.']);

        expect($car->refresh()->status)->toBe(CarStatus::Available);
    });
});
