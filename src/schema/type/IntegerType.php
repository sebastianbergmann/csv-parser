<?php declare(strict_types=1);
/*
 * This file is part of sebastian/csv-parser.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\CsvParser;

use const FILTER_VALIDATE_INT;
use function filter_var;
use function is_finite;
use function is_numeric;
use function preg_match;
use function sprintf;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final class IntegerType extends Type
{
    public function apply(string $value): int
    {
        $toCheck = $value;

        if (preg_match('/^(-?)0+(\d+)$/', $value, $matches) === 1) {
            $toCheck = $matches[1] . $matches[2];
        }

        $integerValue = filter_var($toCheck, FILTER_VALIDATE_INT);

        if ($integerValue !== false) {
            return $integerValue;
        }

        if (!is_numeric($value)) {
            throw new InvalidIntegerException(
                sprintf(
                    '"%s" is not a numeric value',
                    $value,
                ),
            );
        }

        if (preg_match('/^-?\d+$/', $value) === 1) {
            throw new InvalidIntegerException(
                sprintf(
                    '"%s" is out of range for an integer',
                    $value,
                ),
            );
        }

        $floatValue = (float) $value;

        if (!is_finite($floatValue) ||
            $floatValue >= 9223372036854775808.0 ||
            $floatValue < -9223372036854775808.0) {
            throw new InvalidIntegerException(
                sprintf(
                    '"%s" cannot be represented as an integer',
                    $value,
                ),
            );
        }

        $integerValue = (int) $floatValue;

        if ((float) $integerValue !== $floatValue) {
            throw new InvalidIntegerException(
                sprintf(
                    '"%s" cannot be represented as an integer',
                    $value,
                ),
            );
        }

        return $integerValue;
    }
}
