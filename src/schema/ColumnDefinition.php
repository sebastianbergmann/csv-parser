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
     * @psalm-param list<?string> $input
     * @psalm-param array<string, mixed> $output
     *
     * @throws OutOfBoundsException
     */
    public function parse(array $input, array &$output): void
    {
        if (!array_key_exists($this->position - 1, $input)) {
            throw new OutOfBoundsException(
                'Input array does not have an element at position ' . $this->position
            );
        }

        $value = $input[$this->position - 1];

        if ($value === null) {
            throw new InvalidValueException('Input array has element with invalid value at position ' . $this->position);
        }

        $output[$this->name] = $this->type->cast($value);
    }

    /**
     * @psalm-return positive-int
     */
    public function position(): int
    {
        return $this->position;
    }

    /**
     * @psalm-return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }
}
