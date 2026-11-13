<?php

namespace App\Services;

use App\Exceptions\RecallLookupFailed;
use App\Support\SafetyRecall;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

/**
 * Looks up the safety recalls NHTSA lists for a make, model and model year.
 *
 * The lookup is by vehicle, not by VIN: it tells which campaigns cover the
 * model, not whether one car was repaired. Only what NHTSA answers is
 * returned; when it cannot answer, the lookup fails instead of reporting
 * that there is no recall.
 */
class SafetyRecallService
{
    /**
     * NHTSA endpoint that lists the recalls of a vehicle; it is free and needs no key.
     */
    protected const RECALLS_URL = 'https://api.nhtsa.gov/recalls/recallsByVehicle';

    /**
     * Seconds to wait for NHTSA to accept a connection, and for its whole answer.
     */
    protected const CONNECT_TIMEOUT = 5;

    protected const TIMEOUT = 10;

    /**
     * Hours an answer of NHTSA is remembered for a make, model and year. Recalls
     * are published a few times a year at most, so half a day stays current.
     */
    protected const CACHE_HOURS = 12;

    /**
     * Get the recalls NHTSA lists for a vehicle, from the cache when it was asked recently.
     *
     * @return list<array{campaign_number: string, component: ?string, summary: ?string, consequence: ?string, remedy: ?string}>
     *
     * @throws RecallLookupFailed when NHTSA is unavailable; a failure is never remembered
     */
    public function forVehicle(string $make, string $model, int $year): array
    {
        $make = trim($make);
        $model = trim($model);

        return Cache::remember(
            'safety-recalls:'.sha1(Str::lower($make.'|'.$model.'|'.$year)),
            now()->addHours(self::CACHE_HOURS),
            fn () => $this->fetch($make, $model, $year),
        );
    }

    /**
     * Ask NHTSA for the recalls of a vehicle.
     *
     * @return list<array{campaign_number: string, component: ?string, summary: ?string, consequence: ?string, remedy: ?string}>
     *
     * @throws RecallLookupFailed
     */
    protected function fetch(string $make, string $model, int $year): array
    {
        try {
            $response = Http::acceptJson()
                ->connectTimeout(self::CONNECT_TIMEOUT)
                ->timeout(self::TIMEOUT)
                ->get(self::RECALLS_URL, ['make' => $make, 'model' => $model, 'modelYear' => $year]);
        } catch (ConnectionException) {
            throw RecallLookupFailed::unavailable();
        }

        $results = $this->hasAnswered($response) ? $response->json('results') : null;

        if (! is_array($results)) {
            throw RecallLookupFailed::unavailable();
        }

        return SafetyRecall::listFrom($results);
    }

    /**
     * Determine whether NHTSA answered the question. It answers a vehicle it does
     * not know with a 400 that carries an empty list, which is an answer too.
     */
    protected function hasAnswered(Response $response): bool
    {
        return $response->successful() || $response->status() === HttpStatus::HTTP_BAD_REQUEST;
    }
}
