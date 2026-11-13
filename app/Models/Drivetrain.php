<?php

namespace App\Models;

/**
 * Which wheels of a car are driven.
 */
enum Drivetrain: string
{
    case FrontWheel = 'FWD';
    case RearWheel = 'RWD';
    case AllWheel = 'AWD';
    case FourWheel = '4WD';

    /**
     * Get the human-readable name of the drivetrain.
     */
    public function label(): string
    {
        return match ($this) {
            self::FrontWheel => 'Front-wheel drive (FWD)',
            self::RearWheel => 'Rear-wheel drive (RWD)',
            self::AllWheel => 'All-wheel drive (AWD)',
            self::FourWheel => 'Four-wheel drive (4WD)',
        };
    }

    /**
     * Get every drivetrain as a value and label pair, for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $drivetrain) => ['value' => $drivetrain->value, 'label' => $drivetrain->label()],
            self::cases(),
        );
    }
}
