<?php

namespace Database\Seeders;

use App\Enums\CarStatus;
use App\Enums\Drivetrain;
use App\Models\Car;
use App\Models\CarStatusChange;
use App\Models\User;
use App\Services\WikipediaPhotos;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CarSeeder extends Seeder
{
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
     * Technical details of each make and model of the inventory, for the trim on the lot.
     *
     * Consumption is in L/100 km, or its equivalent (Le/100 km) for an electric car,
     * which has neither displacement nor cylinders. The level is how well equipped
     * the trim is ("base", "mid" or "top"); "extras" and "without" adjust the
     * equipment that the level implies.
     *
     * @var array<string, array{engine: string, engine_displacement_l: ?float, cylinders: ?int, drivetrain: Drivetrain, doors: int, seats: int, city_l_per_100km: float, highway_l_per_100km: float, interior_colour: string, level: string, extras?: list<string>, without?: list<string>}>
     */
    protected const TECHNICAL_DETAILS = [
        'Toyota Corolla' => ['engine' => '1.8L I4', 'engine_displacement_l' => 1.8, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 7.9, 'highway_l_per_100km' => 6.1, 'interior_colour' => 'Black cloth', 'level' => 'base'],
        'Toyota RAV4' => ['engine' => '2.5L I4 Hybrid', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 5.8, 'highway_l_per_100km' => 6.3, 'interior_colour' => 'Black cloth', 'level' => 'mid'],
        'Toyota Tacoma' => ['engine' => '3.5L V6', 'engine_displacement_l' => 3.5, 'cylinders' => 6, 'drivetrain' => Drivetrain::FourWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 13.0, 'highway_l_per_100km' => 10.5, 'interior_colour' => 'Black cloth', 'level' => 'mid', 'extras' => ['limited_slip_differential', 'drive_mode_selector', 'all_weather_floor_mats']],
        'Toyota Camry' => ['engine' => '2.5L I4', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 8.6, 'highway_l_per_100km' => 6.1, 'interior_colour' => 'Black synthetic leather', 'level' => 'mid', 'extras' => ['sport_suspension', 'paddle_shifters']],
        'Toyota Highlander' => ['engine' => '3.5L V6', 'engine_displacement_l' => 3.5, 'cylinders' => 6, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 8, 'city_l_per_100km' => 12.0, 'highway_l_per_100km' => 8.9, 'interior_colour' => 'Ash leather', 'level' => 'mid', 'extras' => ['sunroof', 'power_liftgate']],
        'Toyota Prius' => ['engine' => '2.0L I4 Hybrid', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 5, 'seats' => 5, 'city_l_per_100km' => 4.8, 'highway_l_per_100km' => 4.7, 'interior_colour' => 'Black synthetic leather', 'level' => 'mid', 'extras' => ['heated_steering_wheel', 'wireless_charging']],
        'Toyota Sienna' => ['engine' => '3.5L V6', 'engine_displacement_l' => 3.5, 'cylinders' => 6, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 8, 'city_l_per_100km' => 12.5, 'highway_l_per_100km' => 8.9, 'interior_colour' => 'Ash cloth', 'level' => 'base'],
        'Honda Civic' => ['engine' => '1.5L Turbo I4', 'engine_displacement_l' => 1.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 5, 'seats' => 5, 'city_l_per_100km' => 8.5, 'highway_l_per_100km' => 6.3, 'interior_colour' => 'Black leather', 'level' => 'top'],
        'Honda CR-V' => ['engine' => '1.5L Turbo I4', 'engine_displacement_l' => 1.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 8.7, 'highway_l_per_100km' => 7.4, 'interior_colour' => 'Black leather', 'level' => 'mid', 'extras' => ['sunroof', 'power_liftgate', 'heated_steering_wheel']],
        'Honda Accord' => ['engine' => '2.0L I4 Hybrid', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 5.3, 'highway_l_per_100km' => 5.7, 'interior_colour' => 'Black leather', 'level' => 'top', 'extras' => ['ventilated_front_seats', 'head_up_display']],
        'Honda Pilot' => ['engine' => '3.5L V6', 'engine_displacement_l' => 3.5, 'cylinders' => 6, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 7, 'city_l_per_100km' => 12.4, 'highway_l_per_100km' => 9.3, 'interior_colour' => 'Beige leather', 'level' => 'top', 'extras' => ['rear_seat_entertainment', 'trailer_hitch']],
        'Honda HR-V' => ['engine' => '1.8L I4', 'engine_displacement_l' => 1.8, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 8.8, 'highway_l_per_100km' => 7.5, 'interior_colour' => 'Black cloth', 'level' => 'mid', 'extras' => ['sunroof']],
        'Honda Odyssey' => ['engine' => '3.5L V6', 'engine_displacement_l' => 3.5, 'cylinders' => 6, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 8, 'city_l_per_100km' => 12.3, 'highway_l_per_100km' => 8.5, 'interior_colour' => 'Grey leather', 'level' => 'mid', 'extras' => ['sunroof', 'power_liftgate', 'backup_camera']],
        'Ford F-150' => ['engine' => '3.5L Twin-Turbo V6', 'engine_displacement_l' => 3.5, 'cylinders' => 6, 'drivetrain' => Drivetrain::FourWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 13.1, 'highway_l_per_100km' => 10.2, 'interior_colour' => 'Black leather', 'level' => 'top', 'extras' => ['ventilated_front_seats', 'drive_mode_selector']],
        'Ford Mustang' => ['engine' => '5.0L V8', 'engine_displacement_l' => 5.0, 'cylinders' => 8, 'drivetrain' => Drivetrain::RearWheel, 'doors' => 2, 'seats' => 4, 'city_l_per_100km' => 16.1, 'highway_l_per_100km' => 9.9, 'interior_colour' => 'Ebony leather', 'level' => 'top', 'extras' => ['limited_slip_differential', 'performance_brakes', 'sport_exhaust', 'sport_suspension', 'drive_mode_selector', 'ventilated_front_seats'], 'without' => ['sunroof', 'heated_rear_seats', 'wireless_charging']],
        'Ford Escape' => ['engine' => '2.5L I4 Hybrid', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 5.5, 'highway_l_per_100km' => 6.4, 'interior_colour' => 'Ebony leather', 'level' => 'top', 'extras' => ['head_up_display']],
        'Ford Bronco Sport' => ['engine' => '2.0L Turbo I4', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::FourWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 11.1, 'highway_l_per_100km' => 9.0, 'interior_colour' => 'Ebony cloth', 'level' => 'mid', 'extras' => ['drive_mode_selector', 'all_weather_floor_mats']],
        'Ford Maverick' => ['engine' => '2.5L I4 Hybrid', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 5.6, 'highway_l_per_100km' => 7.1, 'interior_colour' => 'Navy cloth', 'level' => 'mid', 'without' => ['power_driver_seat', 'dual_zone_climate']],
        'Ford Mustang Mach-E' => ['engine' => 'Dual electric motors', 'engine_displacement_l' => null, 'cylinders' => null, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 2.3, 'highway_l_per_100km' => 2.6, 'interior_colour' => 'Black synthetic leather', 'level' => 'top', 'extras' => ['panoramic_roof'], 'without' => ['sunroof']],
        'Chevrolet Silverado 1500' => ['engine' => '5.3L V8', 'engine_displacement_l' => 5.3, 'cylinders' => 8, 'drivetrain' => Drivetrain::FourWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 15.1, 'highway_l_per_100km' => 11.7, 'interior_colour' => 'Jet Black cloth', 'level' => 'mid', 'extras' => ['all_weather_floor_mats', 'remote_start']],
        'Chevrolet Bolt EV' => ['engine' => 'Electric motor', 'engine_displacement_l' => null, 'cylinders' => null, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 5, 'seats' => 5, 'city_l_per_100km' => 1.8, 'highway_l_per_100km' => 2.2, 'interior_colour' => 'Black leather', 'level' => 'mid', 'extras' => ['heated_steering_wheel', 'heated_rear_seats', 'wireless_charging', 'digital_instrument_cluster'], 'without' => ['fog_lights']],
        'Chevrolet Equinox' => ['engine' => '1.5L Turbo I4', 'engine_displacement_l' => 1.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 9.6, 'highway_l_per_100km' => 8.0, 'interior_colour' => 'Jet Black cloth', 'level' => 'mid', 'extras' => ['remote_start', 'power_liftgate']],
        'Chevrolet Cruze' => ['engine' => '1.4L Turbo I4', 'engine_displacement_l' => 1.4, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 8.0, 'highway_l_per_100km' => 5.8, 'interior_colour' => 'Jet Black cloth', 'level' => 'base', 'extras' => ['backup_camera', 'remote_start']],
        'GMC Sierra 1500' => ['engine' => '5.3L V8', 'engine_displacement_l' => 5.3, 'cylinders' => 8, 'drivetrain' => Drivetrain::FourWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 15.0, 'highway_l_per_100km' => 11.8, 'interior_colour' => 'Jet Black leather', 'level' => 'top', 'extras' => ['head_up_display', 'all_weather_floor_mats']],
        'Mazda MX-5' => ['engine' => '2.0L I4', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::RearWheel, 'doors' => 2, 'seats' => 2, 'city_l_per_100km' => 9.0, 'highway_l_per_100km' => 7.0, 'interior_colour' => 'Black cloth', 'level' => 'mid', 'extras' => ['limited_slip_differential', 'sport_suspension'], 'without' => ['power_driver_seat', 'dual_zone_climate']],
        'Mazda CX-5' => ['engine' => '2.5L Turbo I4', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 10.8, 'highway_l_per_100km' => 8.7, 'interior_colour' => 'Cocoa Nappa leather', 'level' => 'top', 'extras' => ['ventilated_front_seats', 'head_up_display']],
        'Mazda Mazda3 Sport' => ['engine' => '2.5L I4', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 5, 'seats' => 5, 'city_l_per_100km' => 9.0, 'highway_l_per_100km' => 6.8, 'interior_colour' => 'Black leather', 'level' => 'top', 'extras' => ['head_up_display', 'paddle_shifters'], 'without' => ['heated_rear_seats']],
        'Mazda CX-30' => ['engine' => '2.5L Turbo I4', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 10.5, 'highway_l_per_100km' => 7.9, 'interior_colour' => 'Black leather', 'level' => 'top', 'extras' => ['head_up_display', 'paddle_shifters'], 'without' => ['heated_rear_seats']],
        'Subaru Outback' => ['engine' => '2.4L Turbo H4', 'engine_displacement_l' => 2.4, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 5, 'seats' => 5, 'city_l_per_100km' => 10.9, 'highway_l_per_100km' => 9.2, 'interior_colour' => 'Grey synthetic leather', 'level' => 'mid', 'extras' => ['all_weather_floor_mats', 'drive_mode_selector', 'heated_rear_seats', 'heated_steering_wheel']],
        'Subaru WRX' => ['engine' => '2.4L Turbo H4', 'engine_displacement_l' => 2.4, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 12.3, 'highway_l_per_100km' => 9.0, 'interior_colour' => 'Black suede', 'level' => 'top', 'extras' => ['limited_slip_differential', 'sport_suspension', 'drive_mode_selector'], 'without' => ['remote_start', 'wireless_charging']],
        'Subaru Crosstrek' => ['engine' => '2.5L H4', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 8.8, 'highway_l_per_100km' => 7.0, 'interior_colour' => 'Grey synthetic leather', 'level' => 'mid', 'extras' => ['sunroof', 'wireless_charging']],
        'Subaru Forester' => ['engine' => '2.5L H4', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 9.0, 'highway_l_per_100km' => 7.2, 'interior_colour' => 'Black cloth', 'level' => 'mid', 'extras' => ['panoramic_roof', 'power_liftgate']],
        'Hyundai Elantra' => ['engine' => '2.0L I4', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 7.6, 'highway_l_per_100km' => 5.7, 'interior_colour' => 'Black cloth', 'level' => 'base', 'extras' => ['heated_front_seats', 'heated_steering_wheel']],
        'Hyundai Ioniq 5' => ['engine' => 'Dual electric motors', 'engine_displacement_l' => null, 'cylinders' => null, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 2.1, 'highway_l_per_100km' => 2.6, 'interior_colour' => 'Black cloth', 'level' => 'mid', 'extras' => ['digital_instrument_cluster', 'navigation', 'heated_steering_wheel']],
        'Hyundai Tucson' => ['engine' => '2.5L I4', 'engine_displacement_l' => 2.5, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 9.9, 'highway_l_per_100km' => 8.3, 'interior_colour' => 'Black leather and suede', 'level' => 'mid', 'extras' => ['panoramic_roof', 'power_liftgate', 'digital_instrument_cluster']],
        'Hyundai Kona Electric' => ['engine' => 'Electric motor', 'engine_displacement_l' => null, 'cylinders' => null, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 1.8, 'highway_l_per_100km' => 2.2, 'interior_colour' => 'Black cloth', 'level' => 'base', 'extras' => ['heated_front_seats', 'alloy_wheels']],
        'Kia Telluride' => ['engine' => '3.8L V6', 'engine_displacement_l' => 3.8, 'cylinders' => 6, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 7, 'city_l_per_100km' => 12.6, 'highway_l_per_100km' => 9.7, 'interior_colour' => 'Black Nappa leather', 'level' => 'top', 'extras' => ['ventilated_front_seats', 'head_up_display', 'trailer_hitch']],
        'Kia Soul' => ['engine' => '2.0L I4', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 5, 'seats' => 5, 'city_l_per_100km' => 8.1, 'highway_l_per_100km' => 6.9, 'interior_colour' => 'Black cloth', 'level' => 'mid', 'extras' => ['sunroof', 'wireless_charging']],
        'Kia EV6' => ['engine' => 'Dual electric motors', 'engine_displacement_l' => null, 'cylinders' => null, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 2.0, 'highway_l_per_100km' => 2.6, 'interior_colour' => 'Black synthetic leather', 'level' => 'top', 'extras' => ['ventilated_front_seats']],
        'Kia Forte' => ['engine' => '2.0L I4', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 7.7, 'highway_l_per_100km' => 5.9, 'interior_colour' => 'Black cloth', 'level' => 'mid', 'extras' => ['sunroof']],
        'Volkswagen Golf GTI' => ['engine' => '2.0L Turbo I4', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 5, 'seats' => 5, 'city_l_per_100km' => 9.8, 'highway_l_per_100km' => 7.3, 'interior_colour' => 'Titan Black leather', 'level' => 'top', 'extras' => ['limited_slip_differential', 'sport_suspension', 'drive_mode_selector', 'performance_brakes'], 'without' => ['remote_start']],
        'Volkswagen Tiguan' => ['engine' => '2.0L Turbo I4', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 10.6, 'highway_l_per_100km' => 8.0, 'interior_colour' => 'Titan Black leatherette', 'level' => 'mid', 'extras' => ['panoramic_roof', 'power_liftgate']],
        'Volkswagen Jetta' => ['engine' => '1.4L Turbo I4', 'engine_displacement_l' => 1.4, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 7.8, 'highway_l_per_100km' => 5.9, 'interior_colour' => 'Titan Black leatherette', 'level' => 'mid', 'extras' => ['sunroof', 'rain_sensing_wipers']],
        'BMW 3 Series' => ['engine' => '2.0L Turbo I4', 'engine_displacement_l' => 2.0, 'cylinders' => 4, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 9.5, 'highway_l_per_100km' => 7.0, 'interior_colour' => 'Black synthetic leather', 'level' => 'top', 'extras' => ['paddle_shifters', 'drive_mode_selector'], 'without' => ['heated_rear_seats']],
        'Tesla Model 3' => ['engine' => 'Dual electric motors', 'engine_displacement_l' => null, 'cylinders' => null, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 1.7, 'highway_l_per_100km' => 1.9, 'interior_colour' => 'Black synthetic leather', 'level' => 'top', 'extras' => ['panoramic_roof'], 'without' => ['apple_carplay', 'android_auto', 'satellite_radio', 'sunroof']],
        'Tesla Model Y' => ['engine' => 'Dual electric motors', 'engine_displacement_l' => null, 'cylinders' => null, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 2.0, 'highway_l_per_100km' => 2.3, 'interior_colour' => 'Black and white synthetic leather', 'level' => 'top', 'extras' => ['panoramic_roof', 'performance_brakes', 'sport_suspension'], 'without' => ['apple_carplay', 'android_auto', 'satellite_radio', 'sunroof', 'roof_rails']],
        'Jeep Wrangler Unlimited' => ['engine' => '3.6L V6', 'engine_displacement_l' => 3.6, 'cylinders' => 6, 'drivetrain' => Drivetrain::FourWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 13.8, 'highway_l_per_100km' => 10.1, 'interior_colour' => 'Black cloth', 'level' => 'mid', 'extras' => ['limited_slip_differential', 'all_weather_floor_mats', 'trailer_hitch', 'trailer_wiring_harness'], 'without' => ['roof_rails', 'cargo_cover', 'power_driver_seat', 'dual_zone_climate']],
        'Nissan Rogue' => ['engine' => '1.5L Turbo I3', 'engine_displacement_l' => 1.5, 'cylinders' => 3, 'drivetrain' => Drivetrain::AllWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 8.4, 'highway_l_per_100km' => 6.7, 'interior_colour' => 'Charcoal cloth', 'level' => 'mid', 'extras' => ['panoramic_roof', 'remote_start']],
        'Nissan Micra' => ['engine' => '1.6L I4', 'engine_displacement_l' => 1.6, 'cylinders' => 4, 'drivetrain' => Drivetrain::FrontWheel, 'doors' => 5, 'seats' => 5, 'city_l_per_100km' => 8.6, 'highway_l_per_100km' => 6.6, 'interior_colour' => 'Charcoal cloth', 'level' => 'base'],
        'Ram 1500' => ['engine' => '5.7L V8', 'engine_displacement_l' => 5.7, 'cylinders' => 8, 'drivetrain' => Drivetrain::FourWheel, 'doors' => 4, 'seats' => 5, 'city_l_per_100km' => 15.7, 'highway_l_per_100km' => 11.0, 'interior_colour' => 'Diesel Grey cloth', 'level' => 'mid', 'extras' => ['remote_start', 'trailer_brake_controller']],
    ];

    /**
     * Equipment that each level adds to the one below it, as keys of the CarEquipment catalogue.
     *
     * @var array<string, list<string>>
     */
    protected const EQUIPMENT_BY_LEVEL = [
        'base' => ['abs', 'air_conditioning', 'keyless_entry', 'bluetooth', 'am_fm_radio', 'usb_audio_input', 'steering_wheel_audio_controls', 'daytime_running_lights', 'power_windows'],
        'mid' => ['dual_zone_climate', 'push_button_start', 'satellite_radio', 'led_headlights', 'fog_lights', 'heated_front_seats', 'power_driver_seat', 'alloy_wheels', 'heated_mirrors', 'leather_steering_wheel'],
        'top' => ['adaptive_cruise_control', 'heated_steering_wheel', 'remote_start', 'navigation', 'wireless_charging', 'digital_instrument_cluster', 'premium_sound_system', 'automatic_high_beams', 'led_taillights', 'ambient_interior_lighting', 'heated_rear_seats', 'driver_seat_memory', 'sunroof', 'rain_sensing_wipers', 'auto_dimming_mirror'],
    ];

    /**
     * Equipment that only became common from MODERN_EQUIPMENT_SINCE on, by the level that brings it.
     *
     * @var array<string, list<string>>
     */
    protected const MODERN_EQUIPMENT_BY_LEVEL = [
        'base' => ['backup_camera', 'apple_carplay', 'android_auto', 'automatic_headlights'],
        'mid' => ['automatic_emergency_braking', 'blind_spot_monitoring', 'lane_keeping_assist', 'rear_cross_traffic_alert'],
        'top' => [],
    ];

    /**
     * First model year that gets the modern equipment.
     */
    protected const MODERN_EQUIPMENT_SINCE = 2018;

    /**
     * Number of most recent sales that are kept within the current month, so the dashboard has sales to show.
     */
    protected const SALES_KEPT_IN_CURRENT_MONTH = 3;

    /**
     * Seed the car inventory, each car with a freely licensed photo of its model when there is one,
     * its technical details, its equipment, a cost price and the history behind its status.
     */
    public function run(WikipediaPhotos $wikipedia): void
    {
        $articles = array_values(array_unique(array_filter(array_column(self::INVENTORY, 'article'))));
        $photos = $wikipedia->forArticles($articles);
        $salesperson = User::query()->oldest('id')->first();
        $salesSeeded = 0;

        foreach (self::INVENTORY as $index => $vehicle) {
            $photo = $photos[$vehicle['article']] ?? null;
            $listedAt = now()->subHours($index * self::HOURS_BETWEEN_LISTINGS);
            $details = self::TECHNICAL_DETAILS[$vehicle['make'].' '.$vehicle['model']];
            $statusChanges = $this->statusChanges($vehicle['status'] ?? CarStatus::Available, $listedAt, $salesSeeded);
            $soldAt = $statusChanges[CarStatus::Sold->value] ?? null;

            $factory = $photo === null ? Car::factory()->withoutPhoto() : Car::factory()->state($photo);

            $car = $factory->create([
                ...Arr::except($vehicle, ['article', 'status']),
                ...Arr::except($details, ['level', 'extras', 'without']),
                'equipment' => $this->equipment($vehicle, $details),
                'cost_price' => $this->costPrice($vehicle['price'], $index),
                'status' => $vehicle['status'] ?? CarStatus::Available,
                'sold_on' => $soldAt?->toDateString(),
                'stock_number' => sprintf('STK-%05d', 1001 + $index),
                'received_on' => $listedAt->toDateString(),
                'description' => array_key_exists('description', $vehicle) ? $vehicle['description'] : $this->describe($vehicle),
                'created_at' => $listedAt,
            ]);

            $this->recordStatusChanges($car, $statusChanges, $salesperson);

            $salesSeeded += $soldAt === null ? 0 : 1;
        }

        if (count($photos) < count($articles)) {
            $this->command?->warn('Some car photos could not be fetched from Wikipedia; those cars will show a placeholder.');
        }
    }

    /**
     * Get what the dealership paid for a car: 6 to 14 % under its asking price, to the nearest $50.
     * The margin varies from one listing to the next; a car without an asking price has no cost price either.
     */
    protected function costPrice(?int $price, int $index): ?int
    {
        if ($price === null) {
            return null;
        }

        $marginInPercent = 6 + ($index * 5) % 9;

        return (int) round($price * (100 - $marginInPercent) / 100 / 50) * 50;
    }

    /**
     * Get the equipment of a vehicle: what its level and model year bring, what its body,
     * engine, seats and upholstery imply, then its own extras and exceptions.
     *
     * @param  array{year: int, body_type: string}  $vehicle
     * @param  array{engine: string, seats: int, interior_colour: string, level: string, extras?: list<string>, without?: list<string>}  $details
     * @return list<string>
     */
    protected function equipment(array $vehicle, array $details): array
    {
        $allLevels = array_keys(self::EQUIPMENT_BY_LEVEL);
        $levels = array_slice($allLevels, 0, array_search($details['level'], $allLevels, true) + 1);
        $isAboveBase = $details['level'] !== 'base';
        $isTop = $details['level'] === 'top';
        $isPickup = $vehicle['body_type'] === 'Pickup';
        $isUtility = in_array($vehicle['body_type'], ['SUV', 'Wagon'], true);

        $implied = array_keys(array_filter([
            'turbocharged_engine' => str_contains($details['engine'], 'Turbo'),
            'leather_seats' => preg_match('/(?<!synthetic )leather\b/i', $details['interior_colour']) === 1,
            'third_row_seating' => $details['seats'] >= 7,
            'split_folding_rear_seat' => $details['seats'] > 2 && ! $isPickup,
            'trailer_hitch' => $isPickup,
            'trailer_wiring_harness' => $isPickup,
            'trailer_sway_control' => $isPickup,
            'tow_haul_mode' => $isPickup,
            'tow_package' => $isPickup && $isAboveBase,
            'trailer_brake_controller' => $isPickup && $isTop,
            'roof_rails' => $isUtility && $isAboveBase,
            'cargo_cover' => $isUtility && $isAboveBase,
            'power_liftgate' => $isUtility && $isTop,
        ]));

        $equipment = [
            ...Arr::flatten(Arr::only(self::EQUIPMENT_BY_LEVEL, $levels)),
            ...($vehicle['year'] >= self::MODERN_EQUIPMENT_SINCE ? Arr::flatten(Arr::only(self::MODERN_EQUIPMENT_BY_LEVEL, $levels)) : []),
            ...$implied,
            ...($details['extras'] ?? []),
        ];

        return array_values(array_unique(array_diff($equipment, $details['without'] ?? [])));
    }

    /**
     * Get when a car reached each status after "available", keyed by status value, oldest first.
     *
     * A car on hold was held two thirds of the way between its listing and today. A sold car
     * was sold halfway, or at the start of the current month for the most recent sales when
     * halfway falls before it; every other sale went through a hold first.
     *
     * @return array<string, CarbonInterface>
     */
    protected function statusChanges(CarStatus $status, CarbonInterface $listedAt, int $salesSeeded): array
    {
        $hoursListed = $listedAt->diffInHours(now());

        if ($status === CarStatus::Available) {
            return [];
        }

        if ($status === CarStatus::OnHold) {
            return [CarStatus::OnHold->value => $listedAt->copy()->addHours($hoursListed * 2 / 3)];
        }

        $soldAt = $listedAt->copy()->addHours($hoursListed / 2);

        if ($salesSeeded < self::SALES_KEPT_IN_CURRENT_MONTH) {
            $soldAt = $soldAt->max(now()->startOfMonth()->addHours(10)->min(now()));
        }

        $heldAt = $listedAt->copy()->addHours($listedAt->diffInHours($soldAt) / 2);

        return [
            ...($salesSeeded % 2 === 0 ? [CarStatus::OnHold->value => $heldAt] : []),
            CarStatus::Sold->value => $soldAt,
        ];
    }

    /**
     * Add the status changes of a car to its history, each one starting from the status before it.
     *
     * @param  array<string, CarbonInterface>  $statusChanges
     */
    protected function recordStatusChanges(Car $car, array $statusChanges, ?User $changedBy): void
    {
        $previousStatus = CarStatus::Available;

        foreach ($statusChanges as $status => $changedAt) {
            CarStatusChange::factory()->for($car)->create([
                'user_id' => $changedBy?->id,
                'from_status' => $previousStatus,
                'to_status' => $status,
                'created_at' => $changedAt,
            ]);

            $previousStatus = CarStatus::from($status);
        }
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
