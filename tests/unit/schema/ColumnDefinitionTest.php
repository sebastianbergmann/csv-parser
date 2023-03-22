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
#[UsesClass(StringType::class)]
#[Small]
final class ColumnDefinitionTest extends TestCase
{
    public function testDefinesName(): void
    {
        $this->assertSame('name', $this->column()->name());
    }

    public function testDefinesType(): void
    {
        $this->assertTrue($this->column()->type()->isString());
    }

    private function column(): ColumnDefinition
    {
        return ColumnDefinition::from('name', Type::string());
    }
}
