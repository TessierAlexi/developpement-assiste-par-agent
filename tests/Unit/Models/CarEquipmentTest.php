<?php

use App\Support\CarEquipment;

it('offers ten categories in a fixed order', function () {
    expect(array_column(CarEquipment::catalogue(), 'label'))->toBe([
        'Safety', 'Comfort', 'Technology', 'Audio', 'Lighting',
        'Seating', 'Exterior', 'Interior', 'Towing', 'Performance',
    ]);
});

it('offers about seventy options, each under a unique key', function () {
    $keys = CarEquipment::keys();

    expect(count($keys))->toBeBetween(65, 75)
        ->and(array_unique($keys))->toHaveCount(count($keys));
});

it('describes each option with the value to store and the label to show', function () {
    expect(CarEquipment::catalogue()[0]['options'][0])->toBe([
        'value' => 'abs',
        'label' => 'Anti-lock brakes (ABS)',
    ]);
});

it('groups the selected options by category, in catalogue order', function () {
    $grouped = CarEquipment::grouped(['trailer_hitch', 'blind_spot_monitoring', 'apple_carplay', 'abs']);

    expect($grouped)->toBe([
        ['category' => 'Safety', 'options' => ['Anti-lock brakes (ABS)', 'Blind-spot monitoring']],
        ['category' => 'Technology', 'options' => ['Apple CarPlay']],
        ['category' => 'Towing', 'options' => ['Trailer hitch']],
    ]);
});

it('leaves out options that are not in the catalogue', function () {
    expect(CarEquipment::grouped(['abs', 'flux_capacitor']))->toBe([
        ['category' => 'Safety', 'options' => ['Anti-lock brakes (ABS)']],
    ]);
});

it('returns no category when nothing is selected', function () {
    expect(CarEquipment::grouped([]))->toBe([]);
});
