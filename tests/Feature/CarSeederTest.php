<?php

use App\Models\Car;
use App\Models\CarEquipment;
use App\Models\CarStatus;
use App\Models\CarStatusChange;
use App\Models\Drivetrain;
use App\Models\User;
use App\Models\Vin;
use Database\Seeders\CarSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

/**
 * Fake the Wikipedia API: each article gets a lead image, and each image
 * gets Commons licence metadata.
 */
function fakeWikipediaApi(): void
{
    Http::preventStrayRequests();

    Http::fake([
        'en.wikipedia.org/w/api.php*' => function (Request $request) {
            $titles = explode('|', $request['titles']);

            if ($request['prop'] === 'pageimages') {
                return Http::response(['query' => ['pages' => array_map(fn (string $title) => [
                    'title' => $title,
                    'pageimage' => str_replace(' ', '_', $title).'_front.jpg',
                ], $titles)]]);
            }

            return Http::response(['query' => ['pages' => array_map(fn (string $title) => [
                'title' => $title,
                'imageinfo' => [[
                    'thumburl' => 'https://upload.wikimedia.org/thumb/'.rawurlencode($title).'/960px.jpg',
                    'descriptionurl' => 'https://commons.wikimedia.org/wiki/'.rawurlencode($title),
                    'extmetadata' => [
                        'Artist' => ['value' => '<a href="//commons.wikimedia.org/wiki/User:Jane">Jane Doe</a>'],
                        'LicenseShortName' => ['value' => 'CC BY-SA 4.0'],
                        'LicenseUrl' => ['value' => 'https://creativecommons.org/licenses/by-sa/4.0'],
                        'AttributionRequired' => ['value' => 'true'],
                    ],
                ]],
            ], $titles)]]);
        },
    ]);
}

it('seeds fifty cars', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    expect(Car::count())->toBe(50);
});

it('gives every seeded car with a model photo a credited picture', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $withPhoto = Car::whereNotNull('photo_url')->get();

    expect($withPhoto->count())->toBeGreaterThanOrEqual(40);

    $withPhoto->each(fn (Car $car) => expect($car)
        ->photo_url->toStartWith('https://upload.wikimedia.org/')
        ->photo_source_url->toStartWith('https://commons.wikimedia.org/')
        ->photo_author->toBe('Jane Doe')
        ->photo_licence->toBe('CC BY-SA 4.0')
        ->photo_attribution_required->toBeTrue());
});

it('still seeds the cars without pictures when Wikipedia is unavailable', function () {
    Http::preventStrayRequests();
    Http::fake(['en.wikipedia.org/w/api.php*' => Http::response('Service Unavailable', 503)]);

    $this->seed(CarSeeder::class);

    expect(Car::count())->toBe(50)
        ->and(Car::whereNotNull('photo_url')->count())->toBe(0);
});

it('gives every seeded car its own stock number and received date', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    expect(Car::distinct()->count('stock_number'))->toBe(Car::count())
        ->and(Car::whereNull('received_on')->count())->toBe(0);
});

it('gives every seeded car a valid VIN of its own, never leaving it out', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $vins = Car::pluck('vin');

    expect($vins->filter()->unique())->toHaveCount(50);

    $vins->each(fn (?string $vin) => expect(Vin::isValid((string) $vin))->toBeTrue("'{$vin}' is not a valid VIN"));
});

it('seeds mostly available cars, with a few on hold and a few sold', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $countByStatus = Car::all()->countBy(fn (Car $car) => $car->status->value);

    expect($countByStatus['available'])->toBeGreaterThanOrEqual(30)
        ->and($countByStatus['on_hold'])->toBeBetween(3, 10)
        ->and($countByStatus['sold'])->toBeBetween(3, 10);
});

it('dates the sale of every sold car, and only of sold cars', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    expect(Car::where('status', CarStatus::Sold)->whereNull('sold_on')->count())->toBe(0)
        ->and(Car::where('status', '!=', CarStatus::Sold)->whereNotNull('sold_on')->count())->toBe(0)
        ->and(Car::whereColumn('sold_on', '<', 'received_on')->count())->toBe(0);
});

it('leaves a few gaps in the seeded data, like a real inventory', function (string $column) {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    expect(Car::whereNull($column)->count())->toBeBetween(2, 8);
})->with([
    'no photo' => 'photo_url',
    'no price' => 'price',
    'no description' => 'description',
    'no colour' => 'colour',
]);

it('seeds prices and mileages that are believable for the age of each car', function () {
    $this->travelTo('2026-11-10');
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    Car::all()->each(function (Car $car) {
        $ageInYears = max(1, 2026 - $car->year + 1);

        expect($car->year)->toBeBetween(2012, 2026)
            ->and($car->mileage_km / $ageInYears)->toBeBetween(1_000, 28_000, "{$car->year} {$car->make} {$car->model}: {$car->mileage_km} km");

        if ($car->price !== null) {
            expect($car->price)->toBeBetween(5_000, 90_000, "{$car->year} {$car->make} {$car->model}: \${$car->price}");
        }
    });
});

it('gives every seeded car its technical details', function (string $column) {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    expect(Car::whereNull($column)->count())->toBe(0);
})->with([
    'engine' => 'engine',
    'drivetrain' => 'drivetrain',
    'doors' => 'doors',
    'seats' => 'seats',
    'city consumption' => 'city_l_per_100km',
    'highway consumption' => 'highway_l_per_100km',
    'interior colour' => 'interior_colour',
]);

it('gives a displacement and cylinders to every seeded car with a combustion engine, and to no electric car', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $electric = Car::where('fuel_type', 'Electric');
    $combustion = Car::where('fuel_type', '!=', 'Electric');

    expect($electric->count())->toBeGreaterThan(0)
        ->and($electric->clone()->whereNotNull('engine_displacement_l')->count())->toBe(0)
        ->and($electric->clone()->whereNotNull('cylinders')->count())->toBe(0)
        ->and($combustion->clone()->whereNull('engine_displacement_l')->count())->toBe(0)
        ->and($combustion->clone()->whereNull('cylinders')->count())->toBe(0);
});

it('seeds technical details that match the model and trim of the car', function (string $model, array $expected) {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $car = Car::where('model', $model)->sole();

    expect($car->only(array_keys($expected)))->toEqual($expected);
})->with([
    'compact sedan' => ['Corolla', ['engine' => '1.8L I4', 'engine_displacement_l' => '1.8', 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5]],
    'V8 coupe' => ['Mustang', ['engine' => '5.0L V8', 'engine_displacement_l' => '5.0', 'cylinders' => 8, 'drivetrain' => Drivetrain::RearWheel, 'doors' => 2, 'seats' => 4]],
    'three-row SUV' => ['Telluride', ['engine' => '3.8L V6', 'cylinders' => 6, 'drivetrain' => Drivetrain::AllWheel, 'seats' => 7]],
    'off-road pickup' => ['Tacoma', ['engine' => '3.5L V6', 'drivetrain' => Drivetrain::FourWheel]],
    'electric sedan' => ['Model 3', ['engine' => 'Dual electric motors', 'drivetrain' => Drivetrain::AllWheel, 'city_l_per_100km' => '1.7']],
]);

it('seeds all-wheel drive for every trim that advertises it', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $advertised = Car::all()->filter(fn (Car $car) => Str::contains($car->trim, ['AWD', 'xDrive', '4MOTION']));

    expect($advertised->count())->toBeGreaterThanOrEqual(8);

    $advertised->each(fn (Car $car) => expect($car->drivetrain)->toBe(Drivetrain::AllWheel, "{$car->make} {$car->model} {$car->trim}"));
});

it('seeds a cost price 5 to 15 % under the asking price of every priced car', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $priced = Car::whereNotNull('price')->get();

    expect($priced->whereNull('cost_price'))->toBeEmpty();

    $priced->each(function (Car $car) {
        $margin = ($car->price - $car->cost_price) / $car->price;

        expect($margin)->toBeBetween(0.05, 0.15, "{$car->make} {$car->model}: \${$car->cost_price} for \${$car->price}");
    });
});

it('seeds no cost price for the cars left without an asking price', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    expect(Car::whereNull('price')->whereNotNull('cost_price')->count())->toBe(0);
});

it('seeds margins that differ from one car to the next', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $marginsInPercent = Car::whereNotNull('price')->get()
        ->map(fn (Car $car) => (int) round(($car->price - $car->cost_price) / $car->price * 100))
        ->unique();

    expect($marginsInPercent->count())->toBeGreaterThanOrEqual(5);
});

it('equips every seeded car with distinct options from the equipment catalogue', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    Car::all()->each(function (Car $car) {
        $name = "{$car->make} {$car->model}";

        expect(count($car->equipment ?? []))->toBeGreaterThanOrEqual(8, "{$name} has too little equipment")
            ->and(array_diff($car->equipment, CarEquipment::keys()))->toBeEmpty("{$name} has unknown equipment")
            ->and(array_unique($car->equipment))->toHaveCount(count($car->equipment), "{$name} has duplicate equipment");
    });
});

it('equips a base trim with less than a top trim', function (string $baseModel, string $topModel) {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $base = Car::where('model', $baseModel)->sole();
    $top = Car::where('model', $topModel)->sole();

    expect(count($base->equipment))->toBeLessThan(count($top->equipment) - 10)
        ->and($base->equipment)->not->toContain('leather_seats')
        ->and($top->equipment)->toContain('leather_seats');
})->with([
    'Micra SV against Telluride SX Limited' => ['Micra', 'Telluride'],
    'Elantra Preferred against Accord Touring' => ['Elantra', 'Accord'],
]);

it('seeds equipment that suits the car', function (string $model, array $expected, array $unexpected) {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $equipment = Car::where('model', $model)->sole()->equipment;

    expect(array_diff($expected, $equipment))->toBeEmpty()
        ->and(array_intersect($unexpected, $equipment))->toBeEmpty();
})->with([
    'a pickup tows' => ['F-150', ['trailer_hitch', 'tow_package', 'turbocharged_engine'], ['third_row_seating', 'roof_rails']],
    'a three-row SUV' => ['Pilot', ['third_row_seating', 'roof_rails'], ['trailer_brake_controller', 'turbocharged_engine']],
    'a 2015 subcompact' => ['Micra', ['abs', 'air_conditioning'], ['backup_camera', 'apple_carplay', 'turbocharged_engine']],
    'a two-seat roadster' => ['MX-5', ['limited_slip_differential'], ['split_folding_rear_seat', 'third_row_seating']],
    'a Tesla has no phone mirroring' => ['Model Y', ['panoramic_roof', 'navigation'], ['apple_carplay', 'android_auto', 'turbocharged_engine']],
]);

it('leaves the history of every available car empty', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    expect(CarStatusChange::whereRelation('car', 'status', CarStatus::Available)->count())->toBe(0);
});

it('gives every car on hold or sold a history that starts available and ends with its current status', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $cars = Car::where('status', '!=', CarStatus::Available)->with('statusChanges')->get();

    $cars->each(function (Car $car) {
        $changes = $car->statusChanges->sortBy('created_at')->values();
        $name = "{$car->make} {$car->model}";

        expect($changes)->not->toBeEmpty("{$name} has no history")
            ->and($changes->first()->from_status)->toBe(CarStatus::Available, "{$name} does not start available")
            ->and($changes->last()->to_status)->toBe($car->status, "{$name} does not end with its status");

        $changes->sliding(2)->each(fn ($pair) => expect($pair->last()->from_status)->toBe($pair->first()->to_status, "{$name} has a broken chain"));
    });
});

it('dates every seeded status change after the car was received and never in the future', function () {
    $this->travelTo('2026-11-10 14:00:00');
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $changes = CarStatusChange::with('car')->get();

    expect($changes)->not->toBeEmpty();

    $changes->each(fn (CarStatusChange $change) => expect($change->created_at->toDateTimeString())
        ->toBeGreaterThan($change->car->created_at->toDateTimeString())
        ->toBeLessThanOrEqual('2026-11-10 14:00:00'));
});

it('records the sale of every sold car in its history on its sale date', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $sold = Car::where('status', CarStatus::Sold)->with('statusChanges')->get();

    $sold->each(function (Car $car) {
        $sales = $car->statusChanges->where('to_status', CarStatus::Sold);

        expect($sales)->toHaveCount(1)
            ->and($sales->first()->created_at->toDateString())->toBe($car->sold_on->toDateString());
    });
});

it('seeds some sales that went through a hold and some that did not', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $changesPerSoldCar = Car::where('status', CarStatus::Sold)->withCount('statusChanges')->pluck('status_changes_count');

    expect($changesPerSoldCar)->toContain(1)->toContain(2);
});

it('seeds a few sales in the current month, whatever the day', function (string $today) {
    $this->travelTo($today);
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    $salesThisMonth = Car::whereBetween('sold_on', [today()->startOfMonth(), today()->endOfMonth()])->count();

    expect($salesThisMonth)->toBeGreaterThanOrEqual(2)
        ->and(Car::whereColumn('sold_on', '<', 'received_on')->count())->toBe(0)
        ->and(Car::where('sold_on', '>', today())->count())->toBe(0);
})->with([
    'first day of the month' => '2026-12-01 09:00:00',
    'middle of the month' => '2026-11-10 14:00:00',
    'last day of the month' => '2026-11-30 23:00:00',
]);

it('credits the seeded status changes to the first user when there is one', function () {
    fakeWikipediaApi();
    $user = User::factory()->create();

    $this->seed(CarSeeder::class);

    expect(CarStatusChange::count())->toBeGreaterThan(0)
        ->and(CarStatusChange::where('user_id', '!=', $user->id)->orWhereNull('user_id')->count())->toBe(0);
});
