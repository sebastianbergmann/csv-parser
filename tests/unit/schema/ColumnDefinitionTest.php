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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ColumnDefinition::class)]
#[UsesClass(Type::class)]
#[UsesClass(IntegerType::class)]
#[Small]
final class ColumnDefinitionTest extends TestCase
{
    public function test_Parses_column_from_input_array_into_output_array(): void
    {
        $input  = ['1'];
        $output = [];

        $this->column()->apply($input, $output);

        $this->assertSame(['name' => 1], $output);
    }

    public function testCannotParseColumnFromInputArrayThatDoesNotExist(): void
    {
        $input  = [];
        $output = [];

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Input array does not have an element at position 1');

        $this->column()->apply($input, $output);
    }

    private function column(): ColumnDefinition
    {
        return ColumnDefinition::from(1, 'name', Type::integer());
    }
}
