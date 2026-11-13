<?php

namespace App\Models;

use Closure;

/**
 * Vehicle identification number rules, as used in North America (ISO 3779 with a check digit).
 */
class Vin
{
    /**
     * Number of characters in a VIN.
     */
    public const LENGTH = 17;

    /**
     * Characters allowed in a VIN, each at the offset of its value in the check digit sum.
     * I, O and Q are never used; the gaps keep the remaining letters at their value.
     */
    protected const TRANSLITERATION = '0123456789.ABCDEFGH..JKLMN.P.R..STUVWXYZ';

    /**
     * Weight of each of the 17 positions in the check digit sum; the check digit itself weighs nothing.
     */
    protected const WEIGHTS = [8, 7, 6, 5, 4, 3, 2, 10, 0, 9, 8, 7, 6, 5, 4, 3, 2];

    /**
     * Position of the check digit, counting from zero.
     */
    protected const CHECK_DIGIT_POSITION = 8;

    /**
     * Tidy a VIN as typed, so one entered in lowercase or with spaces around it is accepted.
     */
    public static function normalise(string $vin): string
    {
        return mb_strtoupper(trim($vin));
    }

    /**
     * Get a validation rule that rejects anything but a valid VIN, with a message for the user.
     *
     * @return Closure(string, mixed, Closure): void
     */
    public static function rule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || ! self::isValid($value)) {
                $fail('The VIN must be '.self::LENGTH.' characters, without I, O or Q, and have a valid check digit.');
            }
        };
    }

    /**
     * Determine whether the value is a well-formed VIN whose check digit matches.
     */
    public static function isValid(string $vin): bool
    {
        if (preg_match('/\A[A-HJ-NPR-Z0-9]{'.self::LENGTH.'}\z/', $vin) !== 1) {
            return false;
        }

        return $vin[self::CHECK_DIGIT_POSITION] === self::checkDigit($vin);
    }

    /**
     * Put the right check digit in a 17-character VIN, whatever stands in its place.
     * Used to make up VINs that pass validation, for the factory and the seeder.
     */
    public static function withCheckDigit(string $vin): string
    {
        return substr_replace($vin, self::checkDigit($vin), self::CHECK_DIGIT_POSITION, 1);
    }

    /**
     * Work out the check digit of a 17-character VIN from its other characters.
     */
    protected static function checkDigit(string $vin): string
    {
        $sum = 0;

        foreach (str_split($vin) as $position => $character) {
            $sum += ((int) strpos(self::TRANSLITERATION, $character) % 10) * self::WEIGHTS[$position];
        }

        return $sum % 11 === 10 ? 'X' : (string) ($sum % 11);
    }
}
