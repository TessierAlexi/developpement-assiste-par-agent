<?php

namespace App\Models;

/**
 * Lets a labelled enum list its cases as the choices of a select input or a tab list.
 */
trait OffersOptions
{
    /**
     * Get the human-readable name of the case.
     */
    abstract public function label(): string;

    /**
     * Get every case as a value and label pair, in declaration order.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }
}
