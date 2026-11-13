<?php

namespace App\Support;

/**
 * Turns the recalls NHTSA lists for a vehicle into the details shown for a car.
 *
 * A detail NHTSA does not state stays null, and an entry without a campaign
 * number is left out: nothing is guessed or made up.
 */
class SafetyRecall
{
    /**
     * NHTSA field behind each detail kept for a recall.
     */
    protected const FIELDS = [
        'campaign_number' => 'NHTSACampaignNumber',
        'component' => 'Component',
        'summary' => 'Summary',
        'consequence' => 'Consequence',
        'remedy' => 'Remedy',
    ];

    /**
     * Get the recalls stated by the results of a recallsByVehicle answer, each campaign once, in the order given.
     *
     * @param  array<array-key, mixed>  $results
     * @return list<array{campaign_number: string, component: ?string, summary: ?string, consequence: ?string, remedy: ?string}>
     */
    public static function listFrom(array $results): array
    {
        $recalls = [];

        foreach ($results as $result) {
            $recall = array_map(
                fn (string $field) => self::text(is_array($result) ? $result : [], $field),
                self::FIELDS,
            );

            if ($recall['campaign_number'] !== null) {
                $recalls[$recall['campaign_number']] ??= $recall;
            }
        }

        return array_values($recalls);
    }

    /**
     * Get a field of a recall as text, or null when NHTSA has no data for it.
     *
     * @param  array<array-key, mixed>  $result
     */
    protected static function text(array $result, string $field): ?string
    {
        $value = $result[$field] ?? null;
        $value = is_string($value) ? trim($value) : '';

        return $value === '' ? null : $value;
    }
}
