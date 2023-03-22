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
#[Small]
final class ParserTest extends TestCase
{
    public function test_Parses_CSV_file_according_to_schema(): void
    {
        $schema = Schema::from(
            ColumnDefinition::from('foo', Type::integer()),
            ColumnDefinition::from('bar', Type::float()),
            ColumnDefinition::from('baz', Type::string()),
        );

        $parser = new Parser;

        $this->assertSame(
            [
                [
                    'foo' => 1,
                    'bar' => 2.0,
                    'baz' => '3',
                ],
            ],
            iterator_to_array(
                $parser->parse(
                    __DIR__ . '/../fixture/fixture.csv',
                    $schema
                )
            )
        );
    }
}
