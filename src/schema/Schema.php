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

use function array_is_list;
use function assert;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final class Schema
{
    /**
     * @psalm-var list<ColumnDefinition>
     */
    private readonly array $columnDefinitions;

    public static function from(ColumnDefinition ...$columnDefinitions): self
    {
        assert(array_is_list($columnDefinitions));

        return new self($columnDefinitions);
    }

    /**
     * @psalm-param list<ColumnDefinition> $columnDefinitions
     */
    private function __construct(array $columnDefinitions)
    {
        $this->columnDefinitions = $columnDefinitions;
    }

    /**
     * @psalm-param list<string> $line
     *
     * @psalm-return array<string, mixed>
     */
    public function apply(array $line): array
    {
        $data = [];

        foreach ($this->columnDefinitions as $columnDefinition) {
            $columnDefinition->apply($line, $data);
        }

        return $data;
    }
}
