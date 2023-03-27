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
    private bool $ignoreFirstLine = false;

    /**
     * @psalm-return Generator<int, array<string, bool|int|float|object|string>>
     *
     * @throws CannotReadCsvFileException
     * @throws OutOfBoundsException
     */
    public function parse(string $filename, Schema $schema): Generator
    {
        try {
            $file = new SplFileObject($filename);
        } catch (RuntimeException $e) {
            throw new CannotReadCsvFileException($e->getMessage());
        }

        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($this->separator);

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

    public function ignoreFirstLine(): void
    {
        $this->ignoreFirstLine = true;
    }

    /**
     * @psalm-return Generator<int, array<string, bool|int|float|object|string>>
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
