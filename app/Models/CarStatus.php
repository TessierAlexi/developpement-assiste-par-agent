<?php

namespace App\Models;

/**
 * Where a car stands in the dealership's inventory.
 */
enum CarStatus: string
{
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

    /**
     * Get every status as a value and label pair, for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases(),
        );
    }
}
