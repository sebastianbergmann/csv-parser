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
            'all columns' => [
                [
                    [
                        'foo' => 1,
                        'bar' => 2.0,
                        'baz' => '3',
                        'boo' => true,
                        'boz' => false,
                    ],
                ],
                Schema::from(
                    [
                        1 => ColumnDefinition::from('foo', Type::integer()),
                        2 => ColumnDefinition::from('bar', Type::float()),
                        3 => ColumnDefinition::from('baz', Type::string()),
                        4 => ColumnDefinition::from('boo', Type::boolean()),
                        5 => ColumnDefinition::from('boz', Type::boolean()),
                    ]
                ),
                __DIR__ . '/../fixture/fixture.csv',
            ],

            'subset of columns' => [
                [
                    [
                        'bar' => 2.0,
                    ],
                ],
                Schema::from(
                    [
                        2 => ColumnDefinition::from('bar', Type::float()),
                    ]
                ),
                __DIR__ . '/../fixture/fixture.csv',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function test_Parses_CSV_file_according_to_schema(array $expected, Schema $schema, string $filename): void
    {
        $parser = new Parser;

        $this->assertSame(
            $expected,
            iterator_to_array(
                $parser->parse(
                    $filename,
                    $schema
                )
            )
        );
    }
}
