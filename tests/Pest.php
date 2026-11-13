<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * One recall as NHTSA's recallsByVehicle endpoint lists it for a 2023 Honda Accord,
 * trimmed to the fields the application reads plus a few it ignores.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function nhtsaRecall(array $overrides = []): array
{
    return [
        'Manufacturer' => 'Honda (American Honda Motor Co.)',
        'NHTSACampaignNumber' => '23V782000',
        'parkIt' => false,
        'parkOutSide' => false,
        'ReportReceivedDate' => '21/11/2023',
        'Component' => 'SEAT BELTS:FRONT',
        'Summary' => 'Honda is recalling certain 2023-2024 Accord and HR-V vehicles.',
        'Consequence' => 'A seat belt pretensioner missing a rivet will not properly restrain the occupant.',
        'Remedy' => 'Dealers will inspect and replace the seat belt pretensioner assemblies, free of charge.',
        'ModelYear' => '2023',
        'Make' => 'HONDA',
        'Model' => 'ACCORD',
        ...$overrides,
    ];
}

/**
 * The body NHTSA's recallsByVehicle endpoint returns for the given recalls.
 *
 * @param  list<array<string, mixed>>  $recalls
 * @return array{Count: int, Message: string, results: list<array<string, mixed>>}
 */
function nhtsaRecalls(array $recalls): array
{
    return [
        'Count' => count($recalls),
        'Message' => 'Results returned successfully',
        'results' => $recalls,
    ];
}
