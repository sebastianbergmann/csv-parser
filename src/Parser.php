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

use function is_array;
use Generator;
use RuntimeException;
use SplFileObject;

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
        try {
            $file = new SplFileObject($filename);
        } catch (RuntimeException $e) {
            throw new CannotReadCsvFileException($e->getMessage());
        }

        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        return $this->generator($file, $schema, $ignoreFirstLine);
    }

    /**
     * @psalm-return Generator<int, array<string, int|float|string>>
     */
    private function generator(SplFileObject $file, Schema $schema, bool $ignoreFirstLine): Generator
    {
        $firstLine = true;

        foreach ($file as $line) {
            if ($ignoreFirstLine && $firstLine) {
                $firstLine = false;

                continue;
            }

            if (!is_array($line)) {
                continue;
            }

            $data = [];

            foreach ($schema->columnDefinitions() as $columnDefinition) {
                $columnDefinition->parse($line, $data);
            }

            yield $data;
        }
    }
}
