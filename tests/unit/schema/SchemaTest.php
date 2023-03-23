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

#[CoversClass(Schema::class)]
#[UsesClass(ColumnDefinition::class)]
#[UsesClass(Type::class)]
#[Small]
final class SchemaTest extends TestCase
{
    public function testHasColumnDefinitions(): void
    {
        $column = ColumnDefinition::from(1, 'name', Type::integer());
        $schema = Schema::from([1 => $column]);

        $this->assertCount(1, $schema->columnDefinitions());
        $this->assertArrayHasKey(1, $schema->columnDefinitions());
        $this->assertContains($column, $schema->columnDefinitions());
    }
}
