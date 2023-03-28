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

#[CoversClass(FieldDefinition::class)]
#[UsesClass(Type::class)]
#[UsesClass(IntegerType::class)]
#[Small]
final class FieldDefinitionTest extends TestCase
{
    public function test_Parses_field_from_line_array_into_record_array(): void
    {
        $line   = ['1'];
        $record = [];

        $this->field()->apply($line, $record);

        $this->assertSame(['name' => 1], $record);
    }

    public function testCannotParseFieldFromLineArrayThatDoesNotExist(): void
    {
        $line   = [];
        $record = [];

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Input array does not have an element at position 1');

        $this->field()->apply($line, $record);
    }

    private function field(): FieldDefinition
    {
        return FieldDefinition::from(1, 'name', Type::integer());
    }
}
