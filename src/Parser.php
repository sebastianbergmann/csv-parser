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

use function error_get_last;
use function fclose;
use function fgetcsv;
use function fopen;
use function is_dir;
use function sprintf;
use function strlen;
use Generator;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final class Parser
{
    private string $separator     = ',';
    private string $enclosure     = '"';
    private bool $ignoreFirstLine = false;

    /**
     * @throws CannotReadCsvFileException
     * @throws OutOfBoundsException
     *
     * @return Generator<int, array<string, bool|float|int|object|string>>
     */
    public function parse(string $filename, Schema $schema): Generator
    {
        if (is_dir($filename)) {
            throw new CannotReadCsvFileException(
                sprintf('Cannot read "%s": Is a directory', $filename),
            );
        }

        $file = @fopen($filename, 'r');

        if ($file === false) {
            $error = error_get_last();

            throw new CannotReadCsvFileException(
                $error['message'] ?? sprintf('Cannot read "%s"', $filename),
            );
        }

        return $this->generator($file, $schema);
    }

    /**
     * @throws InvalidSeparatorException
     */
    public function setSeparator(string $separator): void
    {
        if (strlen($separator) !== 1) {
            throw new InvalidSeparatorException;
        }

        $this->separator = $separator;
    }

    /**
     * @throws InvalidEnclosureException
     */
    public function setEnclosure(string $enclosure): void
    {
        if (strlen($enclosure) !== 1) {
            throw new InvalidEnclosureException;
        }

        $this->enclosure = $enclosure;
    }

    public function ignoreFirstLine(): void
    {
        $this->ignoreFirstLine = true;
    }

    /**
     * @param resource $file
     *
     * @return Generator<int, array<string, bool|float|int|object|string>>
     */
    private function generator($file, Schema $schema): Generator
    {
        try {
            $firstLine = true;

            while (($line = fgetcsv($file, null, $this->separator, $this->enclosure, '')) !== false) {
                if ($this->ignoreFirstLine && $firstLine) {
                    $firstLine = false;

                    continue;
                }

                if ($line === [null]) {
                    continue;
                }

                /** @phpstan-ignore argument.type */
                yield $schema->apply($line);
            }
        } finally {
            fclose($file);
        }
    }
}
