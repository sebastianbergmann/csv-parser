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

use function array_shift;
use function file;
use function str_getcsv;
use Generator;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final class Parser
{
    /**
     * @psalm-return Generator<int, array<string, int|float|string>>
     *
     * @throws CannotReadCsvFileException
     * @throws OutOfBoundsException
     */
    public function parse(string $filename, Schema $schema, bool $ignoreFirstLine = true): Generator
    {
        $lines = @file($filename);

        if ($lines === false) {
            throw CannotReadCsvFileException::from($filename);
        }

        if ($ignoreFirstLine) {
            array_shift($lines);
        }

        return $this->generator($lines, $schema);
    }

    /**
     * @psalm-param list<string> $lines
     */
    private function generator(array $lines, Schema $schema): Generator
    {
        foreach ($lines as $line) {
            $data = [];

            foreach ($schema->columnDefinitions() as $columnDefinition) {
                $columnDefinition->parse(str_getcsv($line), $data);
            }

            yield $data;
        }
    }
}
