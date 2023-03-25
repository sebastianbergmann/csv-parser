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
#[UsesClass(Schema::class)]
#[UsesClass(ColumnDefinition::class)]
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
        return [
            'CSV file with header; schema for all columns' => [
                [
                    [
                        'a' => 1,
                        'b' => 2.0,
                        'c' => '3',
                        'd' => true,
                        'e' => false,
                    ],
                ],
                Schema::from(
                    ColumnDefinition::from(1, 'a', Type::integer()),
                    ColumnDefinition::from(2, 'b', Type::float()),
                    ColumnDefinition::from(3, 'c', Type::string()),
                    ColumnDefinition::from(4, 'd', Type::boolean()),
                    ColumnDefinition::from(5, 'e', Type::boolean()),
                ),
                __DIR__ . '/../fixture/fixture_with_header.csv',
                true,
            ],

            'CSV file with header; schema for subset of columns' => [
                [
                    [
                        'b' => 2.0,
                    ],
                ],
                Schema::from(
                    ColumnDefinition::from(2, 'b', Type::float()),
                ),
                __DIR__ . '/../fixture/fixture_with_header.csv',
                true,
            ],

            'CSV file without header; schema for all columns' => [
                [
                    [
                        'a' => 1,
                        'b' => 2.0,
                        'c' => '3',
                        'd' => true,
                        'e' => false,
                    ],
                ],
                Schema::from(
                    ColumnDefinition::from(1, 'a', Type::integer()),
                    ColumnDefinition::from(2, 'b', Type::float()),
                    ColumnDefinition::from(3, 'c', Type::string()),
                    ColumnDefinition::from(4, 'd', Type::boolean()),
                    ColumnDefinition::from(5, 'e', Type::boolean()),
                ),
                __DIR__ . '/../fixture/fixture_without_header.csv',
                false,
            ],

            'CSV file without header; schema for subset of columns' => [
                [
                    [
                        'b' => 2.0,
                    ],
                ],
                Schema::from(
                    ColumnDefinition::from(2, 'b', Type::float()),
                ),
                __DIR__ . '/../fixture/fixture_without_header.csv',
                false,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function test_Parses_CSV_file_according_to_schema(array $expected, Schema $schema, string $filename, bool $ignoreFirstLine): void
    {
        $parser = new Parser;

        $this->assertSame(
            $expected,
            iterator_to_array(
                $parser->parse(
                    $filename,
                    $schema,
                    $ignoreFirstLine,
                )
            )
        );
    }

    public function test_Cannot_read_from_CSV_file_that_does_not_exist(): void
    {
        $this->expectException(CannotReadCsvFileException::class);

        (new Parser)->parse('does_not_exist.csv', Schema::from(), false);
    }
}
