<?php

use App\Models\Car;
use App\Support\Vin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('gives every car a valid VIN of its own', function () {
    $vins = Car::factory()->count(25)->create()->pluck('vin');

    expect($vins->unique())->toHaveCount(25);

    $vins->each(fn (?string $vin) => expect($vin)->not->toBeNull()
        ->and(Vin::isValid($vin))->toBeTrue("{$vin} is not a valid VIN"));
});
