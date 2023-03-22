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

abstract class Type
{
    public static function boolean(): self
    {
        return new BooleanType;
    }

    public static function integer(): self
    {
        return new IntegerType;
    }

    public static function float(): self
    {
        return new FloatType;
    }

    public static function string(): self
    {
        return new StringType;
    }

    private function __construct()
    {
    }

    public function isBoolean(): bool
    {
        return false;
    }

    public function isInteger(): bool
    {
        return false;
    }

    public function isFloat(): bool
    {
        return false;
    }

    public function isString(): bool
    {
        return false;
    }
}
