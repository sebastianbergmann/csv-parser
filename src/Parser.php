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
use function strlen;
use Generator;
use RuntimeException;
use SplFileObject;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final class Parser
{
    private string $separator     = ',';
    private string $enclosure     = '"';
    private string $escape        = '"';
    private bool $ignoreFirstLine = false;

    /**
     * @throws CannotReadCsvFileException
     * @throws OutOfBoundsException
     *
     * @return Generator<int, array<string, bool|float|int|object|string>>
     */
    public function parse(string $filename, Schema $schema): Generator
    {
        try {
            $file = new SplFileObject($filename);
        } catch (RuntimeException $e) {
            throw new CannotReadCsvFileException($e->getMessage());
        }

        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($this->separator, $this->enclosure, $this->escape);

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

    /**
     * @throws InvalidEscapeException
     */
    public function setEscape(string $escape): void
    {
        if (strlen($escape) !== 1) {
            throw new InvalidEscapeException;
        }

        $this->escape = $escape;
    }

    public function ignoreFirstLine(): void
    {
        $this->ignoreFirstLine = true;
    }

    /**
     * @return Generator<int, array<string, bool|float|int|object|string>>
     */
    private function generator(SplFileObject $file, Schema $schema): Generator
    {
        $firstLine = true;

        foreach ($file as $line) {
            if ($this->ignoreFirstLine && $firstLine) {
                $firstLine = false;

                continue;
            }

            if (!is_array($line)) {
                continue;
            }

            yield $schema->apply($line);
        }
    }
}
