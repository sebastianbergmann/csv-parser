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

final class Schema
{
    /**
     * @psalm-var list<ColumnDefinition>
     */
    private array $columns;

    public static function from(ColumnDefinition ...$columns): self
    {
        return new self($columns);
    }

    /**
     * @psalm-param list<ColumnDefinition> $columns
     */
    private function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @psalm-return list<ColumnDefinition>
     */
    public function columns(): array
    {
        return $this->columns;
    }
}
