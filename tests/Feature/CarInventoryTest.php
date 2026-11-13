<?php

use App\Models\Car;
use App\Models\CarStatus;
use App\Models\CarStatusChange;
use App\Models\Drivetrain;
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
        'vin' => '1HGCM82633A004352',
        'status' => 'available',
        'received_on' => '2026-11-03',
        'year' => 2023,
        'make' => 'Honda',
        'model' => 'Civic',
        'trim' => 'Sport',
        'body_type' => 'Sedan',
        'fuel_type' => 'Gasoline',
        'transmission' => 'Manual',
        'colour' => 'Sonic Grey Pearl',
        'interior_colour' => 'Black cloth',
        'engine' => '2.0L I4 DOHC',
        'engine_displacement_l' => 2.0,
        'cylinders' => 4,
        'drivetrain' => 'FWD',
        'doors' => 4,
        'seats' => 5,
        'city_l_per_100km' => 8.9,
        'highway_l_per_100km' => 6.4,
        'mileage_km' => 35_600,
        'price' => 32_400,
        'cost_price' => 27_500,
        'description' => 'One owner, winter tires included.',
        'internal_notes' => 'Trade-in, second key missing.',
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

    it('lists a car that has no price or photo without inventing values', function () {
        $car = Car::factory()->withMissingDetails()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cars/Index')
                ->has('cars.data', 1)
                ->where('cars.data.0.id', $car->id)
                ->where('cars.data.0.price', null)
                ->where('cars.data.0.photo_url', null)
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

    it('shows a car that has no price, colour, description or photo', function () {
        $car = Car::factory()->withMissingDetails()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cars/Show')
                ->where('car.id', $car->id)
                ->where('car.price', null)
                ->where('car.colour', null)
                ->where('car.description', null)
                ->where('car.photo_url', null)
                ->where('car.photo_attribution_required', false)
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
                ->where('defaults.status', 'available')
            );
    });
});

describe('car form', function () {
    it('offers the same status and drivetrain choices', function (string $routeName) {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route($routeName, $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('statuses', [
                    ['value' => 'available', 'label' => 'Available'],
                    ['value' => 'on_hold', 'label' => 'On hold'],
                    ['value' => 'sold', 'label' => 'Sold'],
                ])
                ->where('drivetrains', [
                    ['value' => 'FWD', 'label' => 'Front-wheel drive (FWD)'],
                    ['value' => 'RWD', 'label' => 'Rear-wheel drive (RWD)'],
                    ['value' => 'AWD', 'label' => 'All-wheel drive (AWD)'],
                    ['value' => '4WD', 'label' => 'Four-wheel drive (4WD)'],
                ])
            );
    })->with([
        'on the add form' => 'cars.create',
        'on the edit form' => 'cars.edit',
    ]);

    it('applies the same rule when adding and when editing', function (string $method, string $routeName, string $field, mixed $value, string $message) {
        $this->travelTo('2026-11-10');
        $car = Car::factory()->create(['stock_number' => 'STK-0001']);

        $this->actingAs(User::factory()->create())
            ->{$method}(route($routeName, $car), carFormPayload([$field => $value]))
            ->assertSessionHasErrors([$field => $message]);

        expect(Car::count())->toBe(1)
            ->and($car->refresh()->stock_number)->toBe('STK-0001');
    })->with([
        'adding' => ['post', 'cars.store'],
        'editing' => ['put', 'cars.update'],
    ])->with([
        'a VIN that is not valid' => ['vin', '1HGCM82643A004352', 'The VIN must be 17 characters, without I, O or Q, and have a valid check digit.'],
        'an unknown status' => ['status', 'stolen', 'The selected status is invalid.'],
        'a year before 1900' => ['year', 1899, 'The year field must be between 1900 and 2027.'],
        'an unknown drivetrain' => ['drivetrain', '2WD', 'The selected drivetrain is invalid.'],
        'an engine larger than 10 L' => ['engine_displacement_l', 10.1, 'The engine displacement field must be between 0.5 and 10.'],
        'a displacement with two decimals' => ['engine_displacement_l', 2.25, 'The engine displacement field must have 0-1 decimal places.'],
        'cylinders that are not a number' => ['cylinders', 'six', 'The cylinders field must be an integer.'],
        'a single cylinder' => ['cylinders', 1, 'The cylinders field must be between 2 and 16.'],
        'six doors' => ['doors', 6, 'The doors field must be between 2 and 5.'],
        'no seats' => ['seats', 0, 'The seats field must be between 1 and 15.'],
        'a city consumption over 40' => ['city_l_per_100km', 40.1, 'The city consumption field must be between 1 and 40.'],
        'a highway consumption under 1' => ['highway_l_per_100km', 0.9, 'The highway consumption field must be between 1 and 40.'],
        'a negative cost price' => ['cost_price', -1, 'The cost price field must be at least 0.'],
        'internal notes that are too long' => ['internal_notes', str_repeat('a', 5001), 'The internal notes field must not be greater than 5000 characters.'],
    ]);

    it('rejects a VIN already used by another car', function (string $method, string $routeName) {
        Car::factory()->create(['vin' => '1M8GDM9AXKP042788']);
        $car = Car::factory()->create(['stock_number' => 'STK-0001']);

        $this->actingAs(User::factory()->create())
            ->{$method}(route($routeName, $car), carFormPayload(['vin' => '1M8GDM9AXKP042788']))
            ->assertSessionHasErrors(['vin' => 'The VIN has already been taken.']);

        expect(Car::count())->toBe(2)
            ->and($car->refresh()->vin)->toBeNull();
    })->with([
        'adding' => ['post', 'cars.store'],
        'editing' => ['put', 'cars.update'],
    ]);
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

    it('saves the technical details of the new car', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), carFormPayload())
            ->assertRedirect(route('cars.show', Car::sole()));

        expect(Car::sole())
            ->vin->toBe('1HGCM82633A004352')
            ->interior_colour->toBe('Black cloth')
            ->engine->toBe('2.0L I4 DOHC')
            ->engine_displacement_l->toBe('2.0')
            ->cylinders->toBe(4)
            ->drivetrain->toBe(Drivetrain::FrontWheel)
            ->doors->toBe(4)
            ->seats->toBe(5)
            ->city_l_per_100km->toBe('8.9')
            ->highway_l_per_100km->toBe('6.4')
            ->cost_price->toBe(27_500)
            ->internal_notes->toBe('Trade-in, second key missing.');
    });

    it('adds a car whose technical details are not known yet', function () {
        $unknown = array_fill_keys([
            'vin', 'interior_colour', 'engine', 'engine_displacement_l', 'cylinders', 'drivetrain',
            'doors', 'seats', 'city_l_per_100km', 'highway_l_per_100km', 'cost_price', 'internal_notes',
        ], '');

        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), carFormPayload($unknown))
            ->assertRedirect(route('cars.show', Car::sole()));

        expect(Car::sole()->only(array_keys($unknown)))->each->toBeNull();
    });

    it('stores a VIN typed in lowercase in uppercase', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), carFormPayload(['vin' => ' 1hgcm82633a004352 ']))
            ->assertRedirect(route('cars.show', Car::sole()));

        expect(Car::sole()->vin)->toBe('1HGCM82633A004352');
    });

    it('adds a car that is already sold with today as its sale date', function () {
        $this->travelTo('2026-11-10');

        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), carFormPayload(['status' => 'sold', 'sold_on' => '2026-11-01']))
            ->assertRedirect(route('cars.show', Car::sole()));

        expect(Car::sole())
            ->status->toBe(CarStatus::Sold)
            ->sold_on->toDateString()->toBe('2026-11-10');
    });

    it('adds a car whose price and colour are not known yet', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), carFormPayload(['price' => '', 'colour' => '', 'description' => '']))
            ->assertRedirect(route('cars.show', Car::sole()));

        expect(Car::sole())
            ->price->toBeNull()
            ->colour->toBeNull()
            ->description->toBeNull();
    });

    it('rejects a car with missing details', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), [])
            ->assertSessionHasErrors([
                'stock_number' => 'The stock number field is required.',
                'received_on', 'year', 'make', 'model', 'trim', 'body_type',
                'fuel_type', 'transmission', 'mileage_km', 'status',
            ])
            ->assertSessionDoesntHaveErrors(['colour', 'price', 'vin', 'drivetrain', 'cylinders', 'cost_price']);

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

    it('fills the form with the technical details of the car', function () {
        $car = Car::factory()->withTechnicalDetails()->create([
            'vin' => '1HGCM82633A004352',
            'drivetrain' => Drivetrain::AllWheel,
            'engine_displacement_l' => 2.5,
            'city_l_per_100km' => 9.2,
            'cost_price' => 21_000,
            'internal_notes' => 'Windshield chip to repair.',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('cars.edit', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('car.vin', '1HGCM82633A004352')
                ->where('car.status', 'available')
                ->where('car.drivetrain', 'AWD')
                ->where('car.engine_displacement_l', '2.5')
                ->where('car.city_l_per_100km', '9.2')
                ->where('car.cost_price', 21_000)
                ->where('car.internal_notes', 'Windshield chip to repair.')
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

    it('saves the other details of a car that still has no price or colour', function () {
        $car = Car::factory()->withMissingDetails()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['price' => '', 'colour' => '', 'mileage_km' => 36_000]))
            ->assertRedirect(route('cars.show', $car));

        expect($car->refresh())
            ->mileage_km->toBe(36_000)
            ->price->toBeNull()
            ->colour->toBeNull();
    });

    it('saves the technical details and keeps its own VIN', function () {
        $car = Car::factory()->create(['vin' => '1HGCM82633A004352']);

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['drivetrain' => '4WD', 'cylinders' => 6, 'engine_displacement_l' => '3.5']))
            ->assertRedirect(route('cars.show', $car));

        expect($car->refresh())
            ->vin->toBe('1HGCM82633A004352')
            ->drivetrain->toBe(Drivetrain::FourWheel)
            ->cylinders->toBe(6)
            ->engine_displacement_l->toBe('3.5')
            ->internal_notes->toBe('Trade-in, second key missing.');
    });

    it('marks the car sold through the edit form with today as its sale date', function () {
        $this->travelTo('2026-11-10');
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['status' => 'sold', 'sold_on' => '2026-11-01']))
            ->assertRedirect(route('cars.show', $car));

        expect($car->refresh())
            ->status->toBe(CarStatus::Sold)
            ->sold_on->toDateString()->toBe('2026-11-10');
    });

    it('keeps the sale date of a sold car when its other details are edited', function () {
        $car = Car::factory()->sold('2026-10-01')->create();

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['status' => 'sold', 'price' => 29_900]))
            ->assertRedirect(route('cars.show', $car));

        expect($car->refresh())
            ->price->toBe(29_900)
            ->sold_on->toDateString()->toBe('2026-10-01');
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

describe('tabs', function () {
    it('offers the seven tabs of the car detail page, in order', function () {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('tabs', [
                    ['value' => 'overview', 'label' => 'Overview'],
                    ['value' => 'specifications', 'label' => 'Specifications'],
                    ['value' => 'equipment', 'label' => 'Equipment'],
                    ['value' => 'pricing', 'label' => 'Pricing'],
                    ['value' => 'photos', 'label' => 'Photos'],
                    ['value' => 'history', 'label' => 'History'],
                    ['value' => 'notes', 'label' => 'Notes'],
                ])
            );
    });

    it('opens the Overview tab when the URL names no tab', function () {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('activeTab', 'overview'));
    });

    it('opens the tab named in the URL', function (string $tab) {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', ['car' => $car, 'tab' => $tab]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('activeTab', $tab));
    })->with(['specifications', 'equipment', 'pricing', 'photos', 'history', 'notes']);

    it('falls back to the Overview tab when the URL names an unknown tab', function () {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', ['car' => $car, 'tab' => 'warranty']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('activeTab', 'overview'));
    });
});

describe('equipment', function () {
    it('offers the equipment catalogue in ten categories', function (string $routeName) {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route($routeName, $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('equipmentCatalogue', 10)
                ->where('equipmentCatalogue.0.label', 'Safety')
                ->where('equipmentCatalogue.0.options.0', ['value' => 'abs', 'label' => 'Anti-lock brakes (ABS)'])
                ->where('equipmentCatalogue.9.label', 'Performance')
            );
    })->with([
        'on the add form' => 'cars.create',
        'on the edit form' => 'cars.edit',
    ]);

    it('fills the edit form with the equipment of the car', function () {
        $car = Car::factory()->create(['equipment' => ['abs', 'trailer_hitch']]);

        $this->actingAs(User::factory()->create())
            ->get(route('cars.edit', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('car.equipment', ['abs', 'trailer_hitch']));
    });

    it('saves the checked equipment of a new car', function () {
        $this->actingAs(User::factory()->create())
            ->post(route('cars.store'), carFormPayload(['equipment' => ['abs', 'apple_carplay']]))
            ->assertRedirect();

        expect(Car::sole()->equipment)->toBe(['abs', 'apple_carplay']);
    });

    it('replaces the equipment of a car with the options checked in the edit form', function () {
        $car = Car::factory()->create(['equipment' => ['abs', 'trailer_hitch']]);

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['equipment' => ['abs', 'heated_front_seats']]))
            ->assertRedirect(route('cars.show', $car));

        expect($car->refresh()->equipment)->toBe(['abs', 'heated_front_seats']);
    });

    it('removes all the equipment when every option is unchecked', function () {
        $car = Car::factory()->create(['equipment' => ['abs', 'trailer_hitch']]);

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['equipment' => []]))
            ->assertRedirect(route('cars.show', $car));

        expect($car->refresh()->equipment)->toBe([]);
    });

    it('rejects equipment that is not valid', function (mixed $equipment, string $field, string $message) {
        $car = Car::factory()->create(['equipment' => ['abs']]);

        $this->actingAs(User::factory()->create())
            ->put(route('cars.update', $car), carFormPayload(['equipment' => $equipment]))
            ->assertSessionHasErrors([$field => $message]);

        expect($car->refresh()->equipment)->toBe(['abs']);
    })->with([
        'an option that is not in the catalogue' => [['abs', 'flux_capacitor'], 'equipment.1', 'The selected equipment option is invalid.'],
        'the same option twice' => [['abs', 'abs'], 'equipment.0', 'The equipment option field has a duplicate value.'],
        'a value that is not a list' => ['abs', 'equipment', 'The equipment field must be an array.'],
    ]);

    it('shows only the checked equipment, grouped by category', function () {
        $car = Car::factory()->create(['equipment' => ['trailer_hitch', 'heated_front_seats', 'abs', 'blind_spot_monitoring']]);

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('equipment', [
                    ['category' => 'Safety', 'options' => ['Anti-lock brakes (ABS)', 'Blind-spot monitoring']],
                    ['category' => 'Seating', 'options' => ['Heated front seats']],
                    ['category' => 'Towing', 'options' => ['Trailer hitch']],
                ])
            );
    });

    it('shows no equipment for a car that has none recorded', function () {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('equipment', []));
    });
});

describe('history', function () {
    it('records who changed the status from the car page', function () {
        $car = Car::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('cars.status.update', $car), ['status' => 'on_hold'])
            ->assertRedirect();

        $this->assertDatabaseHas('car_status_changes', [
            'car_id' => $car->id,
            'user_id' => $user->id,
            'from_status' => 'available',
            'to_status' => 'on_hold',
        ]);
    });

    it('records who changed the status from the edit form', function () {
        $car = Car::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('cars.update', $car), carFormPayload(['status' => 'sold']))
            ->assertRedirect(route('cars.show', $car));

        $this->assertDatabaseHas('car_status_changes', [
            'car_id' => $car->id,
            'user_id' => $user->id,
            'from_status' => 'available',
            'to_status' => 'sold',
        ]);
    });

    it('does not record a status change when the status is rejected', function () {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('cars.status.update', $car), ['status' => 'stolen'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseEmpty('car_status_changes');
    });

    it('shows the status changes of the car, most recent first', function () {
        $car = Car::factory()->sold()->create();
        $user = User::factory()->create(['name' => 'Camille Roy']);
        CarStatusChange::factory()->for($car)->for($user)->create([
            'from_status' => CarStatus::Available,
            'to_status' => CarStatus::OnHold,
            'created_at' => '2026-11-03 10:15:00',
        ]);
        CarStatusChange::factory()->for($car)->for($user)->create([
            'from_status' => CarStatus::OnHold,
            'to_status' => CarStatus::Sold,
            'created_at' => '2026-11-08 16:45:00',
        ]);

        $this->actingAs($user)
            ->get(route('cars.show', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('statusChanges', 2)
                ->where('statusChanges.0.from', 'on_hold')
                ->where('statusChanges.0.to', 'sold')
                ->where('statusChanges.0.changed_at', '2026-11-08T16:45:00+00:00')
                ->where('statusChanges.0.user', 'Camille Roy')
                ->where('statusChanges.1.from', 'available')
                ->where('statusChanges.1.to', 'on_hold')
                ->where('statusChanges.1.changed_at', '2026-11-03T10:15:00+00:00')
            );
    });

    it('does not show the status changes of another car', function () {
        $car = Car::factory()->create();
        CarStatusChange::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', $car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('statusChanges', []));
    });

    it('keeps a status change without a name once its user is deleted', function () {
        $change = CarStatusChange::factory()->create();

        $change->user->delete();

        $this->actingAs(User::factory()->create())
            ->get(route('cars.show', $change->car))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('statusChanges', 1)
                ->where('statusChanges.0.user', null)
            );
    });

    it('deletes the status changes of a car along with it', function () {
        $change = CarStatusChange::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('cars.destroy', $change->car))
            ->assertRedirect(route('cars.index'));

        $this->assertModelMissing($change);
    });
});
