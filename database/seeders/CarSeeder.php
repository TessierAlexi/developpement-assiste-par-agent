<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CarSeeder extends Seeder
{
    /**
     * Wikipedia API endpoint used to look up a free lead photo for each model.
     */
    protected const WIKIPEDIA_API = 'https://en.wikipedia.org/w/api.php';

    /**
     * Width in pixels of the photo thumbnails to request.
     */
    protected const PHOTO_WIDTH = 1280;

    /**
     * Realistic inventory: each row is a make, model and trim sold together,
     * plus the Wikipedia article whose lead photo represents the model.
     *
     * @var list<array{year: int, make: string, model: string, trim: string, body_type: string, fuel_type: string, transmission: string, colour: string, mileage_km: int, price: int, article: string}>
     */
    protected const INVENTORY = [
        ['year' => 2022, 'make' => 'Toyota', 'model' => 'Corolla', 'trim' => 'LE', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Classic Silver', 'mileage_km' => 41_200, 'price' => 24_995, 'article' => 'Toyota Corolla'],
        ['year' => 2023, 'make' => 'Toyota', 'model' => 'RAV4', 'trim' => 'XLE Hybrid', 'body_type' => 'SUV', 'fuel_type' => 'Hybrid', 'transmission' => 'eCVT', 'colour' => 'Blueprint', 'mileage_km' => 28_750, 'price' => 41_490, 'article' => 'Toyota RAV4'],
        ['year' => 2021, 'make' => 'Toyota', 'model' => 'Tacoma', 'trim' => 'TRD Off-Road', 'body_type' => 'Pickup', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Army Green', 'mileage_km' => 63_400, 'price' => 46_900, 'article' => 'Toyota Tacoma'],
        ['year' => 2020, 'make' => 'Toyota', 'model' => 'Camry', 'trim' => 'SE', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Midnight Black', 'mileage_km' => 78_300, 'price' => 25_850, 'article' => 'Toyota Camry'],
        ['year' => 2022, 'make' => 'Honda', 'model' => 'Civic', 'trim' => 'Sport Touring Hatchback', 'body_type' => 'Hatchback', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Sonic Grey Pearl', 'mileage_km' => 35_600, 'price' => 32_400, 'article' => 'Honda Civic'],
        ['year' => 2024, 'make' => 'Honda', 'model' => 'CR-V', 'trim' => 'EX-L', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Platinum White Pearl', 'mileage_km' => 12_100, 'price' => 43_200, 'article' => 'Honda CR-V'],
        ['year' => 2023, 'make' => 'Honda', 'model' => 'Accord', 'trim' => 'Touring Hybrid', 'body_type' => 'Sedan', 'fuel_type' => 'Hybrid', 'transmission' => 'eCVT', 'colour' => 'Canyon River Blue', 'mileage_km' => 22_900, 'price' => 42_750, 'article' => 'Honda Accord'],
        ['year' => 2021, 'make' => 'Ford', 'model' => 'F-150', 'trim' => 'Lariat SuperCrew', 'body_type' => 'Pickup', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Antimatter Blue', 'mileage_km' => 71_800, 'price' => 52_995, 'article' => 'Ford F-Series'],
        ['year' => 2019, 'make' => 'Ford', 'model' => 'Mustang', 'trim' => 'GT Premium Fastback', 'body_type' => 'Coupe', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Race Red', 'mileage_km' => 48_500, 'price' => 38_900, 'article' => 'Ford Mustang'],
        ['year' => 2022, 'make' => 'Ford', 'model' => 'Escape', 'trim' => 'Titanium Hybrid', 'body_type' => 'SUV', 'fuel_type' => 'Hybrid', 'transmission' => 'eCVT', 'colour' => 'Oxford White', 'mileage_km' => 39_100, 'price' => 33_450, 'article' => 'Ford Escape'],
        ['year' => 2022, 'make' => 'Chevrolet', 'model' => 'Silverado 1500', 'trim' => 'LT Trail Boss', 'body_type' => 'Pickup', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Summit White', 'mileage_km' => 54_300, 'price' => 54_800, 'article' => 'Chevrolet Silverado'],
        ['year' => 2022, 'make' => 'Chevrolet', 'model' => 'Bolt EV', 'trim' => '2LT', 'body_type' => 'Hatchback', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Bright Blue Metallic', 'mileage_km' => 31_700, 'price' => 27_990, 'article' => 'Chevrolet Bolt'],
        ['year' => 2023, 'make' => 'Chevrolet', 'model' => 'Equinox', 'trim' => 'RS AWD', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Mosaic Black', 'mileage_km' => 26_400, 'price' => 34_250, 'article' => 'Chevrolet Equinox'],
        ['year' => 2021, 'make' => 'Mazda', 'model' => 'MX-5', 'trim' => 'GS-P', 'body_type' => 'Convertible', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Soul Red Crystal', 'mileage_km' => 19_800, 'price' => 33_900, 'article' => 'Mazda MX-5'],
        ['year' => 2024, 'make' => 'Mazda', 'model' => 'CX-5', 'trim' => 'Signature', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Platinum Quartz', 'mileage_km' => 9_600, 'price' => 46_300, 'article' => 'Mazda CX-5'],
        ['year' => 2020, 'make' => 'Mazda', 'model' => 'Mazda3', 'trim' => 'GT Sport', 'body_type' => 'Hatchback', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Machine Grey', 'mileage_km' => 66_200, 'price' => 23_750, 'article' => 'Mazda3'],
        ['year' => 2023, 'make' => 'Subaru', 'model' => 'Outback', 'trim' => 'Wilderness', 'body_type' => 'Wagon', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Geyser Blue', 'mileage_km' => 24_300, 'price' => 44_600, 'article' => 'Subaru Outback'],
        ['year' => 2022, 'make' => 'Subaru', 'model' => 'WRX', 'trim' => 'Sport-tech', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'World Rally Blue', 'mileage_km' => 30_900, 'price' => 37_200, 'article' => 'Subaru WRX'],
        ['year' => 2024, 'make' => 'Subaru', 'model' => 'Crosstrek', 'trim' => 'Onyx', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Offshore Blue', 'mileage_km' => 8_400, 'price' => 35_100, 'article' => 'Subaru Crosstrek'],
        ['year' => 2023, 'make' => 'Hyundai', 'model' => 'Elantra', 'trim' => 'Preferred', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'IVT', 'colour' => 'Fluid Metal', 'mileage_km' => 21_500, 'price' => 23_990, 'article' => 'Hyundai Elantra'],
        ['year' => 2023, 'make' => 'Hyundai', 'model' => 'Ioniq 5', 'trim' => 'Preferred AWD Long Range', 'body_type' => 'SUV', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Gravity Gold Matte', 'mileage_km' => 18_200, 'price' => 49_800, 'article' => 'Hyundai Ioniq 5'],
        ['year' => 2022, 'make' => 'Kia', 'model' => 'Telluride', 'trim' => 'SX Limited', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Everlasting Silver', 'mileage_km' => 45_700, 'price' => 51_250, 'article' => 'Kia Telluride'],
        ['year' => 2023, 'make' => 'Kia', 'model' => 'Soul', 'trim' => 'EX+', 'body_type' => 'Hatchback', 'fuel_type' => 'Gasoline', 'transmission' => 'IVT', 'colour' => 'Mojito Green', 'mileage_km' => 16_900, 'price' => 26_400, 'article' => 'Kia Soul'],
        ['year' => 2021, 'make' => 'Volkswagen', 'model' => 'Golf GTI', 'trim' => 'Autobahn', 'body_type' => 'Hatchback', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Tornado Red', 'mileage_km' => 42_800, 'price' => 31_500, 'article' => 'Volkswagen Golf'],
        ['year' => 2022, 'make' => 'Volkswagen', 'model' => 'Tiguan', 'trim' => 'Comfortline 4MOTION', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Pure Grey', 'mileage_km' => 37_400, 'price' => 32_900, 'article' => 'Volkswagen Tiguan'],
        ['year' => 2021, 'make' => 'BMW', 'model' => '3 Series', 'trim' => '330i xDrive', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Portimao Blue', 'mileage_km' => 52_600, 'price' => 38_750, 'article' => 'BMW 3 Series'],
        ['year' => 2023, 'make' => 'Tesla', 'model' => 'Model 3', 'trim' => 'Long Range AWD', 'body_type' => 'Sedan', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Pearl White', 'mileage_km' => 27_300, 'price' => 47_500, 'article' => 'Tesla Model 3'],
        ['year' => 2024, 'make' => 'Tesla', 'model' => 'Model Y', 'trim' => 'Performance', 'body_type' => 'SUV', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Midnight Silver', 'mileage_km' => 14_800, 'price' => 58_900, 'article' => 'Tesla Model Y'],
        ['year' => 2020, 'make' => 'Jeep', 'model' => 'Wrangler Unlimited', 'trim' => 'Rubicon', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Firecracker Red', 'mileage_km' => 69_500, 'price' => 44_900, 'article' => 'Jeep Wrangler'],
        ['year' => 2024, 'make' => 'Nissan', 'model' => 'Rogue', 'trim' => 'SV Moonroof', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Gun Metallic', 'mileage_km' => 11_300, 'price' => 36_800, 'article' => 'Nissan Rogue'],
    ];

    /**
     * Seed the car inventory, each car with a freely licensed photo of its model.
     */
    public function run(): void
    {
        $articles = array_values(array_unique(array_column(self::INVENTORY, 'article')));
        $photos = $this->fetchPhotos($articles);

        foreach (self::INVENTORY as $index => $vehicle) {
            $factory = isset($photos[$vehicle['article']])
                ? Car::factory()->state($photos[$vehicle['article']])
                : Car::factory()->withoutPhoto();

            $factory->create([
                ...Arr::except($vehicle, 'article'),
                'description' => $this->describe($vehicle),
                'created_at' => now()->subHours($index * 9),
            ]);
        }

        if (count($photos) < count($articles)) {
            $this->command?->warn('Some car photos could not be fetched from Wikipedia; those cars will show a placeholder.');
        }
    }

    /**
     * Look up the free lead photo of each Wikipedia article, with its credit.
     *
     * @param  array<int, string>  $articles
     * @return array<string, array{photo_url: string, photo_source_url: ?string, photo_author: ?string, photo_licence: ?string, photo_licence_url: ?string, photo_attribution_required: bool}>
     */
    protected function fetchPhotos(array $articles): array
    {
        $pageImages = $this->queryWikipedia([
            'prop' => 'pageimages',
            'piprop' => 'name',
            'pilicense' => 'free',
            'redirects' => 1,
        ], $articles);

        $fileTitles = collect($pageImages)
            ->filter(fn (array $page) => isset($page['pageimage']))
            ->map(fn (array $page) => 'File:'.str_replace('_', ' ', $page['pageimage']));

        $imageInfo = $this->queryWikipedia([
            'prop' => 'imageinfo',
            'iiprop' => 'url|extmetadata',
            'iiurlwidth' => self::PHOTO_WIDTH,
            'iiextmetadatafilter' => 'Artist|LicenseShortName|LicenseUrl|AttributionRequired',
        ], $fileTitles->values()->all());

        return $fileTitles
            ->map(fn (string $fileTitle) => isset($imageInfo[$fileTitle]['imageinfo'][0])
                ? $this->photoAttributes($imageInfo[$fileTitle]['imageinfo'][0])
                : null)
            ->filter()
            ->all();
    }

    /**
     * Query the Wikipedia API for the given titles, keyed by the requested title.
     *
     * @param  array<string, string|int>  $parameters
     * @param  array<int, string>  $titles
     * @return array<string, array<string, mixed>>
     */
    protected function queryWikipedia(array $parameters, array $titles): array
    {
        if ($titles === []) {
            return [];
        }

        try {
            $response = Http::withUserAgent(config('app.name').' car inventory seeder (Laravel HTTP client)')
                ->timeout(15)
                ->get(self::WIKIPEDIA_API, [
                    'action' => 'query',
                    'format' => 'json',
                    'formatversion' => 2,
                    'titles' => implode('|', $titles),
                    ...$parameters,
                ]);
        } catch (ConnectionException) {
            return [];
        }

        if ($response->failed()) {
            return [];
        }

        return $this->pagesByRequestedTitle($response, $titles);
    }

    /**
     * Map each requested title to its page, following title normalisation and redirects.
     *
     * @param  array<int, string>  $titles
     * @return array<string, array<string, mixed>>
     */
    protected function pagesByRequestedTitle(Response $response, array $titles): array
    {
        $pages = collect($response->json('query.pages', []))->keyBy('title');

        $renames = collect([...$response->json('query.normalized', []), ...$response->json('query.redirects', [])])
            ->pluck('to', 'from');

        return collect($titles)
            ->mapWithKeys(function (string $title) use ($pages, $renames) {
                $normalised = $renames->get($title, $title);

                return [$title => $pages->get($renames->get($normalised, $normalised))];
            })
            ->filter()
            ->all();
    }

    /**
     * Build the photo columns from a Commons image info record.
     *
     * @param  array<string, mixed>  $info
     * @return array{photo_url: string, photo_source_url: ?string, photo_author: ?string, photo_licence: ?string, photo_licence_url: ?string, photo_attribution_required: bool}
     */
    protected function photoAttributes(array $info): array
    {
        $metadata = fn (string $key): ?string => isset($info['extmetadata'][$key]['value'])
            ? Str::limit(trim(html_entity_decode(strip_tags($info['extmetadata'][$key]['value']))), 250)
            : null;

        return [
            'photo_url' => $info['thumburl'] ?? $info['url'],
            'photo_source_url' => $info['descriptionurl'] ?? null,
            'photo_author' => $metadata('Artist'),
            'photo_licence' => $metadata('LicenseShortName'),
            'photo_licence_url' => $metadata('LicenseUrl'),
            'photo_attribution_required' => $metadata('AttributionRequired') === 'true',
        ];
    }

    /**
     * Write a short listing description for a vehicle.
     *
     * @param  array{year: int, make: string, model: string, trim: string, body_type: string, fuel_type: string, transmission: string, colour: string, mileage_km: int}  $vehicle
     */
    protected function describe(array $vehicle): string
    {
        return sprintf(
            '%d %s %s %s in %s with %s km. %s %s, %s transmission. One owner, clean history, safety inspected.',
            $vehicle['year'],
            $vehicle['make'],
            $vehicle['model'],
            $vehicle['trim'],
            $vehicle['colour'],
            number_format($vehicle['mileage_km']),
            $vehicle['fuel_type'],
            Str::lower($vehicle['body_type']),
            Str::lower($vehicle['transmission']),
        );
    }
}
