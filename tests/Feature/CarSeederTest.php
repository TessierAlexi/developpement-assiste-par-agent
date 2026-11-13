<?php

use App\Models\Car;
use App\Models\CarStatus;
use App\Models\Vin;
use Database\Seeders\CarSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

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
