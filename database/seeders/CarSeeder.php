<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarStatus;
use Carbon\CarbonInterface;
use Database\Factories\CarFactory;
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
     * Hours between two consecutive listings, so the inventory spans about two months.
     */
    protected const HOURS_BETWEEN_LISTINGS = 30;

    /**
     * Realistic inventory, newest listing first: each row is a make, model and
     * trim sold together, plus the Wikipedia article whose lead photo represents
     * the model. Cars are available unless a status says otherwise.
     *
     * Like a real database, some rows have gaps: a null colour, price or
     * article (no photo), or an explicitly null description.
     *
     * @var list<array{year: int, make: string, model: string, trim: string, body_type: string, fuel_type: string, transmission: string, colour: ?string, mileage_km: int, price: ?int, article: ?string, status?: CarStatus, description?: null}>
     */
    protected const INVENTORY = [
        ['year' => 2022, 'make' => 'Toyota', 'model' => 'Corolla', 'trim' => 'LE', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Classic Silver', 'mileage_km' => 71_200, 'price' => 21_995, 'article' => 'Toyota Corolla'],
        ['year' => 2023, 'make' => 'Toyota', 'model' => 'RAV4', 'trim' => 'XLE Hybrid', 'body_type' => 'SUV', 'fuel_type' => 'Hybrid', 'transmission' => 'eCVT', 'colour' => 'Blueprint', 'mileage_km' => 52_800, 'price' => 38_490, 'article' => 'Toyota RAV4'],
        ['year' => 2021, 'make' => 'Toyota', 'model' => 'Tacoma', 'trim' => 'TRD Off-Road', 'body_type' => 'Pickup', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Army Green', 'mileage_km' => 94_300, 'price' => 41_900, 'article' => 'Toyota Tacoma'],
        ['year' => 2020, 'make' => 'Toyota', 'model' => 'Camry', 'trim' => 'SE', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Midnight Black', 'mileage_km' => 108_400, 'price' => 23_850, 'article' => 'Toyota Camry', 'status' => CarStatus::OnHold],
        ['year' => 2019, 'make' => 'Toyota', 'model' => 'Highlander', 'trim' => 'XLE AWD', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Celestial Silver', 'mileage_km' => 132_600, 'price' => 29_900, 'article' => 'Toyota Highlander'],
        ['year' => 2024, 'make' => 'Toyota', 'model' => 'Prius', 'trim' => 'XLE AWD', 'body_type' => 'Hatchback', 'fuel_type' => 'Hybrid', 'transmission' => 'eCVT', 'colour' => null, 'mileage_km' => 31_400, 'price' => 36_750, 'article' => 'Toyota Prius'],
        ['year' => 2018, 'make' => 'Toyota', 'model' => 'Sienna', 'trim' => 'LE', 'body_type' => 'Minivan', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Blizzard Pearl', 'mileage_km' => 151_900, 'price' => 22_500, 'article' => null, 'status' => CarStatus::Sold],
        ['year' => 2022, 'make' => 'Honda', 'model' => 'Civic', 'trim' => 'Sport Touring Hatchback', 'body_type' => 'Hatchback', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Sonic Grey Pearl', 'mileage_km' => 63_700, 'price' => 29_400, 'article' => 'Honda Civic'],
        ['year' => 2024, 'make' => 'Honda', 'model' => 'CR-V', 'trim' => 'EX-L', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Platinum White Pearl', 'mileage_km' => 34_100, 'price' => 39_900, 'article' => 'Honda CR-V'],
        ['year' => 2023, 'make' => 'Honda', 'model' => 'Accord', 'trim' => 'Touring Hybrid', 'body_type' => 'Sedan', 'fuel_type' => 'Hybrid', 'transmission' => 'eCVT', 'colour' => 'Canyon River Blue', 'mileage_km' => 47_900, 'price' => 38_750, 'article' => 'Honda Accord'],
        ['year' => 2020, 'make' => 'Honda', 'model' => 'Pilot', 'trim' => 'Touring', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Modern Steel', 'mileage_km' => 118_200, 'price' => null, 'article' => 'Honda Pilot'],
        ['year' => 2021, 'make' => 'Honda', 'model' => 'HR-V', 'trim' => 'Sport', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Aegean Blue', 'mileage_km' => 82_500, 'price' => 23_900, 'article' => 'Honda HR-V'],
        ['year' => 2017, 'make' => 'Honda', 'model' => 'Odyssey', 'trim' => 'EX-L', 'body_type' => 'Minivan', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'White Diamond Pearl', 'mileage_km' => 164_300, 'price' => 18_900, 'article' => 'Honda Odyssey (North America)', 'status' => CarStatus::Sold, 'description' => null],
        ['year' => 2021, 'make' => 'Ford', 'model' => 'F-150', 'trim' => 'Lariat SuperCrew', 'body_type' => 'Pickup', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Antimatter Blue', 'mileage_km' => 98_800, 'price' => 46_995, 'article' => 'Ford F-Series'],
        ['year' => 2019, 'make' => 'Ford', 'model' => 'Mustang', 'trim' => 'GT Premium Fastback', 'body_type' => 'Coupe', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Race Red', 'mileage_km' => 61_500, 'price' => 36_900, 'article' => 'Ford Mustang'],
        ['year' => 2022, 'make' => 'Ford', 'model' => 'Escape', 'trim' => 'Titanium Hybrid', 'body_type' => 'SUV', 'fuel_type' => 'Hybrid', 'transmission' => 'eCVT', 'colour' => 'Oxford White', 'mileage_km' => 68_100, 'price' => 28_450, 'article' => 'Ford Escape', 'status' => CarStatus::OnHold],
        ['year' => 2023, 'make' => 'Ford', 'model' => 'Bronco Sport', 'trim' => 'Badlands', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Cactus Grey', 'mileage_km' => 44_700, 'price' => 36_200, 'article' => 'Ford Bronco Sport'],
        ['year' => 2022, 'make' => 'Ford', 'model' => 'Maverick', 'trim' => 'XLT Hybrid', 'body_type' => 'Pickup', 'fuel_type' => 'Hybrid', 'transmission' => 'eCVT', 'colour' => 'Area 51', 'mileage_km' => 59_300, 'price' => 31_800, 'article' => 'Ford Maverick (2022)', 'description' => null],
        ['year' => 2023, 'make' => 'Ford', 'model' => 'Mustang Mach-E', 'trim' => 'Premium AWD', 'body_type' => 'SUV', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Grabber Blue', 'mileage_km' => 38_600, 'price' => 43_900, 'article' => 'Ford Mustang Mach-E'],
        ['year' => 2022, 'make' => 'Chevrolet', 'model' => 'Silverado 1500', 'trim' => 'LT Trail Boss', 'body_type' => 'Pickup', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Summit White', 'mileage_km' => 79_300, 'price' => 47_800, 'article' => 'Chevrolet Silverado'],
        ['year' => 2022, 'make' => 'Chevrolet', 'model' => 'Bolt EV', 'trim' => '2LT', 'body_type' => 'Hatchback', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Bright Blue Metallic', 'mileage_km' => 51_700, 'price' => 23_990, 'article' => 'Chevrolet Bolt', 'status' => CarStatus::Sold],
        ['year' => 2023, 'make' => 'Chevrolet', 'model' => 'Equinox', 'trim' => 'RS AWD', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Mosaic Black', 'mileage_km' => 49_400, 'price' => 28_250, 'article' => 'Chevrolet Equinox'],
        ['year' => 2016, 'make' => 'Chevrolet', 'model' => 'Cruze', 'trim' => 'LT', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => null, 'mileage_km' => 172_800, 'price' => 8_900, 'article' => 'Chevrolet Cruze'],
        ['year' => 2021, 'make' => 'GMC', 'model' => 'Sierra 1500', 'trim' => 'AT4', 'body_type' => 'Pickup', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Onyx Black', 'mileage_km' => 101_200, 'price' => null, 'article' => null],
        ['year' => 2021, 'make' => 'Mazda', 'model' => 'MX-5', 'trim' => 'GS-P', 'body_type' => 'Convertible', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Soul Red Crystal', 'mileage_km' => 28_800, 'price' => 31_900, 'article' => 'Mazda MX-5'],
        ['year' => 2024, 'make' => 'Mazda', 'model' => 'CX-5', 'trim' => 'Signature', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Platinum Quartz', 'mileage_km' => 27_600, 'price' => 40_300, 'article' => 'Mazda CX-5'],
        ['year' => 2020, 'make' => 'Mazda', 'model' => 'Mazda3 Sport', 'trim' => 'GT', 'body_type' => 'Hatchback', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Machine Grey', 'mileage_km' => 96_200, 'price' => 21_750, 'article' => 'Mazda3', 'status' => CarStatus::OnHold],
        ['year' => 2023, 'make' => 'Mazda', 'model' => 'CX-30', 'trim' => 'GT Turbo', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Polymetal Grey', 'mileage_km' => 41_300, 'price' => 33_400, 'article' => 'Mazda CX-30', 'description' => null],
        ['year' => 2023, 'make' => 'Subaru', 'model' => 'Outback', 'trim' => 'Wilderness', 'body_type' => 'Wagon', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Geyser Blue', 'mileage_km' => 54_300, 'price' => 39_600, 'article' => 'Subaru Outback'],
        ['year' => 2022, 'make' => 'Subaru', 'model' => 'WRX', 'trim' => 'Sport-tech', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'World Rally Blue', 'mileage_km' => 57_900, 'price' => 34_200, 'article' => 'Subaru WRX'],
        ['year' => 2024, 'make' => 'Subaru', 'model' => 'Crosstrek', 'trim' => 'Onyx', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Offshore Blue', 'mileage_km' => 26_400, 'price' => 32_100, 'article' => 'Subaru Crosstrek'],
        ['year' => 2019, 'make' => 'Subaru', 'model' => 'Forester', 'trim' => 'Touring', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Jasper Green', 'mileage_km' => 124_700, 'price' => 22_900, 'article' => 'Subaru Forester', 'status' => CarStatus::Sold],
        ['year' => 2023, 'make' => 'Hyundai', 'model' => 'Elantra', 'trim' => 'Preferred', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'IVT', 'colour' => 'Fluid Metal', 'mileage_km' => 48_500, 'price' => 21_990, 'article' => null],
        ['year' => 2023, 'make' => 'Hyundai', 'model' => 'Ioniq 5', 'trim' => 'Preferred AWD Long Range', 'body_type' => 'SUV', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Gravity Gold Matte', 'mileage_km' => 42_200, 'price' => null, 'article' => 'Hyundai Ioniq 5'],
        ['year' => 2022, 'make' => 'Hyundai', 'model' => 'Tucson', 'trim' => 'N Line', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Amazon Grey', 'mileage_km' => 66_900, 'price' => 30_900, 'article' => 'Hyundai Tucson'],
        ['year' => 2021, 'make' => 'Hyundai', 'model' => 'Kona Electric', 'trim' => 'Preferred', 'body_type' => 'SUV', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Pulse Red', 'mileage_km' => 73_400, 'price' => 24_500, 'article' => 'Hyundai Kona', 'status' => CarStatus::OnHold],
        ['year' => 2022, 'make' => 'Kia', 'model' => 'Telluride', 'trim' => 'SX Limited', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Everlasting Silver', 'mileage_km' => 75_700, 'price' => 44_250, 'article' => 'Kia Telluride'],
        ['year' => 2023, 'make' => 'Kia', 'model' => 'Soul', 'trim' => 'EX+', 'body_type' => 'Hatchback', 'fuel_type' => 'Gasoline', 'transmission' => 'IVT', 'colour' => 'Mojito Green', 'mileage_km' => 39_900, 'price' => null, 'article' => 'Kia Soul'],
        ['year' => 2024, 'make' => 'Kia', 'model' => 'EV6', 'trim' => 'Land AWD', 'body_type' => 'SUV', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Runway Red', 'mileage_km' => 29_800, 'price' => 46_900, 'article' => 'Kia EV6'],
        ['year' => 2020, 'make' => 'Kia', 'model' => 'Forte', 'trim' => 'EX', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'IVT', 'colour' => null, 'mileage_km' => 112_300, 'price' => 15_900, 'article' => 'Kia Forte', 'description' => null],
        ['year' => 2021, 'make' => 'Volkswagen', 'model' => 'Golf GTI', 'trim' => 'Autobahn', 'body_type' => 'Hatchback', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Tornado Red', 'mileage_km' => 72_800, 'price' => 28_500, 'article' => 'Volkswagen Golf'],
        ['year' => 2022, 'make' => 'Volkswagen', 'model' => 'Tiguan', 'trim' => 'Comfortline 4MOTION', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Pure Grey', 'mileage_km' => 67_400, 'price' => 29_900, 'article' => 'Volkswagen Tiguan', 'status' => CarStatus::Sold],
        ['year' => 2019, 'make' => 'Volkswagen', 'model' => 'Jetta', 'trim' => 'Highline', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => null, 'mileage_km' => 119_600, 'price' => 17_900, 'article' => null],
        ['year' => 2021, 'make' => 'BMW', 'model' => '3 Series', 'trim' => '330i xDrive', 'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Portimao Blue', 'mileage_km' => 82_600, 'price' => 34_750, 'article' => 'BMW 3 Series'],
        ['year' => 2023, 'make' => 'Tesla', 'model' => 'Model 3', 'trim' => 'Long Range AWD', 'body_type' => 'Sedan', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Pearl White', 'mileage_km' => 57_300, 'price' => 37_500, 'article' => 'Tesla Model 3', 'status' => CarStatus::OnHold],
        ['year' => 2024, 'make' => 'Tesla', 'model' => 'Model Y', 'trim' => 'Performance', 'body_type' => 'SUV', 'fuel_type' => 'Electric', 'transmission' => 'Single-speed', 'colour' => 'Midnight Silver', 'mileage_km' => 36_800, 'price' => 51_900, 'article' => 'Tesla Model Y'],
        ['year' => 2020, 'make' => 'Jeep', 'model' => 'Wrangler Unlimited', 'trim' => 'Rubicon', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Firecracker Red', 'mileage_km' => 99_500, 'price' => 40_900, 'article' => 'Jeep Wrangler', 'status' => CarStatus::Sold],
        ['year' => 2024, 'make' => 'Nissan', 'model' => 'Rogue', 'trim' => 'SV Moonroof', 'body_type' => 'SUV', 'fuel_type' => 'Gasoline', 'transmission' => 'CVT', 'colour' => 'Gun Metallic', 'mileage_km' => 33_300, 'price' => 32_800, 'article' => 'Nissan Rogue'],
        ['year' => 2015, 'make' => 'Nissan', 'model' => 'Micra', 'trim' => 'SV', 'body_type' => 'Hatchback', 'fuel_type' => 'Gasoline', 'transmission' => 'Manual', 'colour' => 'Metallic Blue', 'mileage_km' => 148_200, 'price' => 6_900, 'article' => 'Nissan Micra', 'status' => CarStatus::Sold, 'description' => null],
        ['year' => 2022, 'make' => 'Ram', 'model' => '1500', 'trim' => 'Big Horn Crew Cab', 'body_type' => 'Pickup', 'fuel_type' => 'Gasoline', 'transmission' => 'Automatic', 'colour' => 'Granite Crystal', 'mileage_km' => 88_400, 'price' => 42_500, 'article' => 'Ram pickup', 'status' => CarStatus::OnHold],
    ];

    /**
     * Seed the car inventory, each car with a freely licensed photo of its model when there is one.
     */
    public function run(): void
    {
        $articles = array_values(array_unique(array_filter(array_column(self::INVENTORY, 'article'))));
        $photos = $this->fetchPhotos($articles);

        foreach (self::INVENTORY as $index => $vehicle) {
            $photo = $photos[$vehicle['article']] ?? null;
            $listedAt = now()->subHours($index * self::HOURS_BETWEEN_LISTINGS);

            $factory = $photo === null ? Car::factory()->withoutPhoto() : Car::factory()->state($photo);

            $this->withStatus($factory, $vehicle['status'] ?? CarStatus::Available, $listedAt)->create([
                ...Arr::except($vehicle, ['article', 'status']),
                'stock_number' => sprintf('STK-%05d', 1001 + $index),
                'received_on' => $listedAt->toDateString(),
                'description' => array_key_exists('description', $vehicle) ? $vehicle['description'] : $this->describe($vehicle),
                'created_at' => $listedAt,
            ]);
        }

        if (count($photos) < count($articles)) {
            $this->command?->warn('Some car photos could not be fetched from Wikipedia; those cars will show a placeholder.');
        }
    }

    /**
     * Apply the status of a seeded car; a sold car was sold halfway between its listing and today.
     */
    protected function withStatus(CarFactory $factory, CarStatus $status, CarbonInterface $listedAt): CarFactory
    {
        return match ($status) {
            CarStatus::Available => $factory,
            CarStatus::OnHold => $factory->onHold(),
            CarStatus::Sold => $factory->sold($listedAt->copy()->addHours($listedAt->diffInHours(now()) / 2)->toDateString()),
        };
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
     * Write a short listing description for a vehicle, leaving out the colour when it is unknown.
     *
     * @param  array{year: int, make: string, model: string, trim: string, body_type: string, fuel_type: string, transmission: string, colour: ?string, mileage_km: int}  $vehicle
     */
    protected function describe(array $vehicle): string
    {
        return sprintf(
            '%d %s %s %s%s with %s km. %s %s, %s transmission. One owner, clean history, safety inspected.',
            $vehicle['year'],
            $vehicle['make'],
            $vehicle['model'],
            $vehicle['trim'],
            $vehicle['colour'] === null ? '' : ' in '.$vehicle['colour'],
            number_format($vehicle['mileage_km']),
            $vehicle['fuel_type'],
            Str::lower($vehicle['body_type']),
            Str::lower($vehicle['transmission']),
        );
    }
}
