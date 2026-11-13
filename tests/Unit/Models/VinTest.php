<?php

use App\Support\Vin;

it('accepts a VIN whose check digit matches', function (string $vin) {
    expect(Vin::isValid($vin))->toBeTrue();
})->with([
    'a numeric check digit' => '1HGCM82633A004352',
    'an X check digit' => '1M8GDM9AXKP042788',
]);

it('rejects a VIN', function (string $vin) {
    expect(Vin::isValid($vin))->toBeFalse();
})->with([
    'that is empty' => '',
    'with 16 characters' => '1HGCM82633A00435',
    'with 18 characters' => '1HGCM82633A0043521',
    'with the letter I' => '1HGCM82633I004352',
    'with the letter O' => '1HGCM82633O004352',
    'with the letter Q' => '1HGCM82633Q004352',
    'with a symbol' => '1HGCM82633A00435-',
    'in lowercase' => '1hgcm82633a004352',
    'with the wrong check digit' => '1HGCM82643A004352',
    'with two characters swapped' => '1HGCM82633A004325',
]);

it('completes a VIN with the check digit that makes it valid', function (string $vin, string $completed) {
    expect(Vin::withCheckDigit($vin))->toBe($completed);
})->with([
    'a numeric check digit' => ['1HGCM826_3A004352', '1HGCM82633A004352'],
    'an X check digit' => ['1M8GDM9A0KP042788', '1M8GDM9AXKP042788'],
    'a check digit that is already right' => ['1HGCM82633A004352', '1HGCM82633A004352'],
]);
