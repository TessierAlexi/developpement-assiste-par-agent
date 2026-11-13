<?php

use App\Models\SafetyRecall;

it('keeps the campaign number, component, summary, consequence and remedy of each recall', function () {
    $recalls = SafetyRecall::listFrom([[
        'Manufacturer' => 'Honda (American Honda Motor Co.)',
        'NHTSACampaignNumber' => '23V782000',
        'parkIt' => false,
        'Component' => 'SEAT BELTS:FRONT',
        'Summary' => 'Honda is recalling certain 2023-2024 Accord vehicles.',
        'Consequence' => 'The occupant may not be properly restrained.',
        'Remedy' => 'Dealers will replace the pretensioner, free of charge.',
        'ModelYear' => '2023',
    ]]);

    expect($recalls)->toBe([[
        'campaign_number' => '23V782000',
        'component' => 'SEAT BELTS:FRONT',
        'summary' => 'Honda is recalling certain 2023-2024 Accord vehicles.',
        'consequence' => 'The occupant may not be properly restrained.',
        'remedy' => 'Dealers will replace the pretensioner, free of charge.',
    ]]);
});

it('leaves a detail empty when NHTSA does not state it', function (mixed $missing) {
    $recalls = SafetyRecall::listFrom([[
        'NHTSACampaignNumber' => '23V782000',
        'Component' => 'SEAT BELTS:FRONT',
        'Summary' => 'Honda is recalling certain 2023-2024 Accord vehicles.',
        'Consequence' => $missing,
        'Remedy' => 'Dealers will replace the pretensioner, free of charge.',
    ]]);

    expect($recalls[0]['consequence'])->toBeNull();
})->with([
    'an empty string' => '',
    'only spaces' => '   ',
    'null' => null,
    'something that is not text' => [['unexpected']],
]);

it('leaves a detail empty when NHTSA omits its field', function () {
    $recalls = SafetyRecall::listFrom([['NHTSACampaignNumber' => '23V782000']]);

    expect($recalls)->toBe([[
        'campaign_number' => '23V782000',
        'component' => null,
        'summary' => null,
        'consequence' => null,
        'remedy' => null,
    ]]);
});

it('trims the spaces around a detail', function () {
    $recalls = SafetyRecall::listFrom([[
        'NHTSACampaignNumber' => ' 23V782000 ',
        'Component' => "  SEAT BELTS:FRONT\n",
    ]]);

    expect($recalls[0])
        ->campaign_number->toBe('23V782000')
        ->component->toBe('SEAT BELTS:FRONT');
});

it('skips an entry that cannot be identified as a recall', function (mixed $entry) {
    $recalls = SafetyRecall::listFrom([
        $entry,
        ['NHTSACampaignNumber' => '23V858000', 'Component' => 'FUEL SYSTEM'],
    ]);

    expect($recalls)->toHaveCount(1)
        ->and($recalls[0]['campaign_number'])->toBe('23V858000');
})->with([
    'without a campaign number' => [['Component' => 'SEAT BELTS:FRONT', 'Summary' => 'Something is wrong.']],
    'with an empty campaign number' => [['NHTSACampaignNumber' => '', 'Component' => 'SEAT BELTS:FRONT']],
    'that is not a record' => ['23V782000'],
]);

it('lists a campaign reported twice only once', function () {
    $recalls = SafetyRecall::listFrom([
        ['NHTSACampaignNumber' => '23V782000', 'Component' => 'SEAT BELTS:FRONT'],
        ['NHTSACampaignNumber' => '23V782000', 'Component' => 'SEAT BELTS:FRONT'],
    ]);

    expect($recalls)->toHaveCount(1);
});

it('lists nothing when NHTSA lists nothing', function () {
    expect(SafetyRecall::listFrom([]))->toBe([]);
});
