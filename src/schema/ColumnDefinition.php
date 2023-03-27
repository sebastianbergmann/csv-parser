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

use function array_key_exists;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final class ColumnDefinition
{
    /**
     * @psalm-var positive-int
     */
    private readonly int $position;

    /**
     * @psalm-var non-empty-string
     */
    private readonly string $name;
    private readonly Type $type;

    /**
     * @psalm-param positive-int $position
     * @psalm-param non-empty-string $name
     */
    public static function from(int $position, string $name, Type $type): self
    {
        return new self($position, $name, $type);
    }

    /**
     * @psalm-param positive-int $position
     * @psalm-param non-empty-string $name
     */
    private function __construct(int $position, string $name, Type $type)
    {
        $this->position = $position;
        $this->name     = $name;
        $this->type     = $type;
    }

    /**
     * @psalm-param list<string> $input
     * @psalm-param array<string, bool|int|float|object|string> $output
     *
     * @throws OutOfBoundsException
     */
    public function apply(array $input, array &$output): void
    {
        if (!array_key_exists($this->position - 1, $input)) {
            throw new OutOfBoundsException(
                'Input array does not have an element at position ' . $this->position
            );
        }

        $value = $input[$this->position - 1];

        $output[$this->name] = $this->type->apply($value);
    }
}
