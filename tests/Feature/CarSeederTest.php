<?php

use App\Models\Car;
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

it('gives every seeded car a credited picture', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    expect(Car::count())->toBeGreaterThanOrEqual(25)
        ->and(Car::whereNull('photo_url')->count())->toBe(0)
        ->and(Car::first())
        ->photo_url->toStartWith('https://upload.wikimedia.org/')
        ->photo_author->toBe('Jane Doe')
        ->photo_licence->toBe('CC BY-SA 4.0')
        ->photo_attribution_required->toBeTrue();
});

it('still seeds the cars without pictures when Wikipedia is unavailable', function () {
    Http::preventStrayRequests();
    Http::fake(['en.wikipedia.org/w/api.php*' => Http::response('Service Unavailable', 503)]);

    $this->seed(CarSeeder::class);

    expect(Car::count())->toBeGreaterThanOrEqual(25)
        ->and(Car::whereNotNull('photo_url')->count())->toBe(0);
});

it('gives every seeded car its own stock number and received date', function () {
    fakeWikipediaApi();

    $this->seed(CarSeeder::class);

    expect(Car::distinct()->count('stock_number'))->toBe(Car::count())
        ->and(Car::whereNull('received_on')->count())->toBe(0);
});
