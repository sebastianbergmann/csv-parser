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

use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Parser::class)]
#[CoversClass(InvalidSeparatorException::class)]
#[CoversClass(InvalidEnclosureException::class)]
#[CoversClass(InvalidEscapeException::class)]
#[UsesClass(Schema::class)]
#[UsesClass(FieldDefinition::class)]
#[UsesClass(Type::class)]
#[UsesClass(FloatType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(StringType::class)]
#[UsesClass(BooleanType::class)]
#[Small]
final class ParserTest extends TestCase
{
    public static function provider(): array
    {
        $allFieldsSchema = Schema::from(
            FieldDefinition::from(1, 'a', Type::integer()),
            FieldDefinition::from(2, 'b', Type::float()),
            FieldDefinition::from(3, 'c', Type::string()),
            FieldDefinition::from(4, 'd', Type::boolean()),
            FieldDefinition::from(5, 'e', Type::boolean()),
        );

        $allFieldsExpectation = [
            [
                'a' => 1,
                'b' => 2.0,
                'c' => '3',
                'd' => true,
                'e' => false,
            ],
        ];

        $singleFieldSchema = Schema::from(
            FieldDefinition::from(2, 'b', Type::float()),
        );

        $singleFieldExpectation = [
            [
                'b' => 2.0,
            ],
        ];

        return [
            'CSV file with header; schema for all fields' => [
                $allFieldsExpectation,
                $allFieldsSchema,
                __DIR__ . '/../fixture/fixture_with_header.csv',
                true,
                null,
                null,
            ],

            'CSV file with header; schema for subset of fields' => [
                $singleFieldExpectation,
                $singleFieldSchema,
                __DIR__ . '/../fixture/fixture_with_header.csv',
                true,
                null,
                null,
            ],

            'CSV file without header; schema for all fields' => [
                $allFieldsExpectation,
                $allFieldsSchema,
                __DIR__ . '/../fixture/fixture_without_header.csv',
                false,
                null,
                null,
            ],

            'CSV file without header; schema for subset of fields' => [
                $singleFieldExpectation,
                $singleFieldSchema,
                __DIR__ . '/../fixture/fixture_without_header.csv',
                false,
                null,
                null,
            ],

            'CSV file with enclosed values (default enclosure)' => [
                $allFieldsExpectation,
                $allFieldsSchema,
                __DIR__ . '/../fixture/fixture_enclosed_values.csv',
                false,
                null,
                null,
            ],

            'CSV file with enclosed values (non-default enclosure)' => [
                $allFieldsExpectation,
                $allFieldsSchema,
                __DIR__ . '/../fixture/fixture_enclosed_values_non_default_enclosure.csv',
                false,
                null,
                '\'',
            ],

            'CSV file with non-default separator' => [
                $allFieldsExpectation,
                $allFieldsSchema,
                __DIR__ . '/../fixture/fixture_non_default_separator.csv',
                false,
                ';',
                null,
            ],

            'CSV file with escaped values (default escape)' => [
                [
                    [
                        'a' => 'aaa',
                        'b' => 'b"bb',
                        'c' => 'ccc',
                    ],
                ],
                Schema::from(
                    FieldDefinition::from(
                        1,
                        'a',
                        Type::string(),
                    ),
                    FieldDefinition::from(
                        2,
                        'b',
                        Type::string(),
                    ),
                    FieldDefinition::from(
                        3,
                        'c',
                        Type::string(),
                    ),
                ),
                __DIR__ . '/../fixture/fixture_escaped_values.csv',
                false,
                null,
                null,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function test_Parses_CSV_file_according_to_schema(array $expected, Schema $schema, string $filename, bool $ignoreFirstLine, ?string $separator, ?string $enclosure): void
    {
        $parser = new Parser;

        if ($separator !== null) {
            $parser->setSeparator($separator);
        }

        if ($enclosure !== null) {
            $parser->setEnclosure($enclosure);
        }

        if ($ignoreFirstLine) {
            $parser->ignoreFirstLine();
        }

        $this->assertSame(
            $expected,
            iterator_to_array(
                $parser->parse(
                    $filename,
                    $schema,
                )
            )
        );
    }

    public function test_Cannot_read_from_CSV_file_that_does_not_exist(): void
    {
        $this->expectException(CannotReadCsvFileException::class);

        (new Parser)->parse('does_not_exist.csv', Schema::from());
    }

    public function testRejectsInvalidSeparator(): void
    {
        $parser = new Parser;

        $this->expectException(InvalidSeparatorException::class);
        $this->expectExceptionMessage('Separator must be a single-byte character');

        $parser->setSeparator('..');
    }

    public function testRejectsInvalidEnclosure(): void
    {
        $parser = new Parser;

        $this->expectException(InvalidEnclosureException::class);
        $this->expectExceptionMessage('Enclosure must be a single-byte character');

        $parser->setEnclosure('..');
    }

    public function testRejectsInvalidEscape(): void
    {
        $parser = new Parser;

        $this->expectException(InvalidEscapeException::class);
        $this->expectExceptionMessage('Escape must be a single-byte character');

        $parser->setEscape('..');
    }
}
