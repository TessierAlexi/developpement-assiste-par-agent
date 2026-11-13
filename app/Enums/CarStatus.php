<?php

namespace App\Enums;

use App\Support\OffersOptions;

/**
 * Where a car stands in the dealership's inventory.
 */
enum CarStatus: string
{
    use OffersOptions;

    case Available = 'available';
    case OnHold = 'on_hold';
    case Sold = 'sold';

    /**
     * Get the human-readable name of the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::OnHold => 'On hold',
            self::Sold => 'Sold',
        };
    }
}
