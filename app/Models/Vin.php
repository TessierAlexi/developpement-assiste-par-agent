<?php

namespace App\Models;

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
     * Determine whether the value is a well-formed VIN whose check digit matches.
     */
    public static function isValid(string $vin): bool
    {
        if (preg_match('/\A[A-HJ-NPR-Z0-9]{'.self::LENGTH.'}\z/', $vin) !== 1) {
            return false;
        }

        $sum = 0;

        foreach (str_split($vin) as $position => $character) {
            $sum += (strpos(self::TRANSLITERATION, $character) % 10) * self::WEIGHTS[$position];
        }

        $checkDigit = $sum % 11 === 10 ? 'X' : (string) ($sum % 11);

        return $vin[self::CHECK_DIGIT_POSITION] === $checkDigit;
    }
}
