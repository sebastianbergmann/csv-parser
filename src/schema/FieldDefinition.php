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
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final readonly class FieldDefinition
{
    /**
     * @var positive-int
     */
    private int $position;

    /**
     * @var non-empty-string
     */
    private string $name;
    private Type $type;

    /**
     * @param positive-int     $position
     * @param non-empty-string $name
     */
    public static function from(int $position, string $name, Type $type): self
    {
        return new self($position, $name, $type);
    }

    /**
     * @param positive-int     $position
     * @param non-empty-string $name
     */
    private function __construct(int $position, string $name, Type $type)
    {
        $this->position = $position;
        $this->name     = $name;
        $this->type     = $type;
    }

    /**
     * @param list<string>                                $input
     * @param array<string, bool|float|int|object|string> $output
     *
     * @throws OutOfBoundsException
     */
    public function apply(array $input, array &$output): void
    {
        if (!array_key_exists($this->position - 1, $input)) {
            throw new OutOfBoundsException(
                'Input array does not have an element at position ' . $this->position,
            );
        }

        $value = $input[$this->position - 1];

        $output[$this->name] = $this->type->apply($value);
    }
}
