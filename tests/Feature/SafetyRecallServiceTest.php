<?php

use App\Exceptions\RecallLookupFailed;
use App\Services\SafetyRecallService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const NHTSA_RECALLS_URL = 'api.nhtsa.gov/recalls/recallsByVehicle*';

it('asks NHTSA for the recalls of the make, model and year', function () {
    Http::preventStrayRequests();
    Http::fake([NHTSA_RECALLS_URL => Http::response(nhtsaRecalls([nhtsaRecall()]))]);

    app(SafetyRecallService::class)->forVehicle('Honda', 'CR-V', 2023);

    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && $request->url() === 'https://api.nhtsa.gov/recalls/recallsByVehicle?make=Honda&model=CR-V&modelYear=2023');
});

it('returns the recalls NHTSA lists, in the same order', function () {
    Http::preventStrayRequests();
    Http::fake([NHTSA_RECALLS_URL => Http::response(nhtsaRecalls([
        nhtsaRecall(),
        nhtsaRecall([
            'NHTSACampaignNumber' => '23V858000',
            'Component' => 'FUEL SYSTEM, GASOLINE:DELIVERY:FUEL PUMP',
            'Summary' => 'The fuel pump inside the fuel tank may fail.',
            'Consequence' => 'Fuel pump failure can cause an engine stall while driving.',
            'Remedy' => 'Dealers will replace the fuel pump module, free of charge.',
        ]),
    ]))]);

    $recalls = app(SafetyRecallService::class)->forVehicle('Honda', 'Accord', 2023);

    expect($recalls)->toBe([
        [
            'campaign_number' => '23V782000',
            'component' => 'SEAT BELTS:FRONT',
            'summary' => 'Honda is recalling certain 2023-2024 Accord and HR-V vehicles.',
            'consequence' => 'A seat belt pretensioner missing a rivet will not properly restrain the occupant.',
            'remedy' => 'Dealers will inspect and replace the seat belt pretensioner assemblies, free of charge.',
        ],
        [
            'campaign_number' => '23V858000',
            'component' => 'FUEL SYSTEM, GASOLINE:DELIVERY:FUEL PUMP',
            'summary' => 'The fuel pump inside the fuel tank may fail.',
            'consequence' => 'Fuel pump failure can cause an engine stall while driving.',
            'remedy' => 'Dealers will replace the fuel pump module, free of charge.',
        ],
    ]);
});

it('returns no recall when NHTSA lists none', function (int $status) {
    Http::preventStrayRequests();
    Http::fake([NHTSA_RECALLS_URL => Http::response(nhtsaRecalls([]), $status)]);

    expect(app(SafetyRecallService::class)->forVehicle('Mazda', 'MX-5', 2021))->toBe([]);
})->with([
    'for a vehicle it knows' => 200,
    'for a vehicle it does not know, which it answers with a 400' => 400,
]);

it('fails rather than report no recall when NHTSA cannot answer', function (Closure $response) {
    Http::preventStrayRequests();
    Http::fake([NHTSA_RECALLS_URL => $response()]);

    expect(fn () => app(SafetyRecallService::class)->forVehicle('Honda', 'Accord', 2023))
        ->toThrow(RecallLookupFailed::class, 'The NHTSA recall service is unavailable right now. Try again later.');
})->with([
    'because the connection fails or times out' => fn () => Http::failedConnection(),
    'because of a server error' => fn () => Http::response('Internal Server Error', 500),
    'because of a server error that carries a list' => fn () => Http::response(nhtsaRecalls([]), 503),
    'with a body that is not JSON' => fn () => Http::response('<html>Maintenance</html>'),
    'with a body without a list of results' => fn () => Http::response(['Count' => 0, 'Message' => 'Results returned successfully']),
    'with a 400 that carries no list of results' => fn () => Http::response(['message' => 'Bad Request'], 400),
]);

it('asks NHTSA only once for the same make, model and year, however they are typed', function () {
    Http::preventStrayRequests();
    Http::fake([NHTSA_RECALLS_URL => Http::response(nhtsaRecalls([nhtsaRecall()]))]);
    $service = app(SafetyRecallService::class);

    $first = $service->forVehicle('Honda', 'Accord', 2023);
    $second = $service->forVehicle(' HONDA', 'accord ', 2023);

    expect($second)->toBe($first)->toHaveCount(1);
    Http::assertSentCount(1);
});

it('asks NHTSA again for another make, model or year', function (string $make, string $model, int $year) {
    Http::preventStrayRequests();
    Http::fake([NHTSA_RECALLS_URL => Http::response(nhtsaRecalls([nhtsaRecall()]))]);
    $service = app(SafetyRecallService::class);

    $service->forVehicle('Honda', 'Accord', 2023);
    $service->forVehicle($make, $model, $year);

    Http::assertSentCount(2);
})->with([
    'another make' => ['Acura', 'Accord', 2023],
    'another model' => ['Honda', 'Civic', 2023],
    'another year' => ['Honda', 'Accord', 2022],
]);

it('remembers that a vehicle has no recall', function () {
    Http::preventStrayRequests();
    Http::fake([NHTSA_RECALLS_URL => Http::response(nhtsaRecalls([]))]);
    $service = app(SafetyRecallService::class);

    $service->forVehicle('Mazda', 'MX-5', 2021);
    $service->forVehicle('Mazda', 'MX-5', 2021);

    Http::assertSentCount(1);
});

it('asks NHTSA again once the remembered recalls are twelve hours old', function () {
    Http::preventStrayRequests();
    Http::fake([NHTSA_RECALLS_URL => Http::response(nhtsaRecalls([nhtsaRecall()]))]);
    $service = app(SafetyRecallService::class);

    $service->forVehicle('Honda', 'Accord', 2023);
    $this->travel(11)->hours();
    $service->forVehicle('Honda', 'Accord', 2023);
    Http::assertSentCount(1);

    $this->travel(61)->minutes();
    $service->forVehicle('Honda', 'Accord', 2023);
    Http::assertSentCount(2);
});

it('does not remember a failure, so the next check asks NHTSA again', function () {
    Http::preventStrayRequests();
    Http::fake([NHTSA_RECALLS_URL => Http::sequence()
        ->pushFailedConnection()
        ->push(nhtsaRecalls([nhtsaRecall()]))]);
    $service = app(SafetyRecallService::class);

    expect(fn () => $service->forVehicle('Honda', 'Accord', 2023))->toThrow(RecallLookupFailed::class);

    expect($service->forVehicle('Honda', 'Accord', 2023))->toHaveCount(1);
});
