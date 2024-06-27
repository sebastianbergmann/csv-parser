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
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final readonly class Schema
{
    /**
     * @var list<FieldDefinition>
     */
    private array $fieldDefinitions;

    public static function from(FieldDefinition ...$fieldDefinitions): self
    {
        assert(array_is_list($fieldDefinitions));

        return new self($fieldDefinitions);
    }

    /**
     * @param list<FieldDefinition> $fieldDefinitions
     */
    private function __construct(array $fieldDefinitions)
    {
        $this->fieldDefinitions = $fieldDefinitions;
    }

    /**
     * @param list<string> $line
     *
     * @return array<string, bool|float|int|object|string>
     */
    public function apply(array $line): array
    {
        $data = [];

        foreach ($this->fieldDefinitions as $fieldDefinition) {
            $fieldDefinition->apply($line, $data);
        }

        return $data;
    }
}
