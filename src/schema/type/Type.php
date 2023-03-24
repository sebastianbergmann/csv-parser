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

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
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

    abstract public function apply(string $value): mixed;
}
