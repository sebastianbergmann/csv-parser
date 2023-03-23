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
final class ColumnDefinition
{
    /**
     * @psalm-var positive-int
     */
    private readonly int $position;
    private readonly string $name;
    private readonly Type $type;

    /**
     * @psalm-param positive-int $position
     */
    public static function from(int $position, string $name, Type $type): self
    {
        return new self($position, $name, $type);
    }

    /**
     * @psalm-param positive-int $position
     */
    private function __construct(int $position, string $name, Type $type)
    {
        $this->position = $position;
        $this->name     = $name;
        $this->type     = $type;
    }

    public function parse(array $input, array &$output): void
    {
        $output[$this->name] = $this->type->cast($input[$this->position - 1]);
    }

    /**
     * @psalm-return positive-int
     */
    public function position(): int
    {
        return $this->position;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }
}
