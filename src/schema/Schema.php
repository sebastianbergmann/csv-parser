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
final class Schema
{
    /**
     * @psalm-var array<int, ColumnDefinition>
     */
    private readonly array $columnDefinitions;

    /**
     * @psalm-param array<int, ColumnDefinition> $columnDefinitions
     */
    public static function from(array $columnDefinitions): self
    {
        return new self($columnDefinitions);
    }

    /**
     * @psalm-param array<int, ColumnDefinition> $columnDefinitions
     */
    private function __construct(array $columnDefinitions)
    {
        $this->columnDefinitions = $columnDefinitions;
    }

    /**
     * @psalm-return array<int, ColumnDefinition>
     */
    public function columnDefinitions(): array
    {
        return $this->columnDefinitions;
    }
}
