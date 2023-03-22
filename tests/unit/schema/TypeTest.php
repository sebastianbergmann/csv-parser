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
use PHPUnit\Framework\TestCase;

#[CoversClass(Type::class)]
#[CoversClass(IntegerType::class)]
#[CoversClass(FloatType::class)]
#[CoversClass(StringType::class)]
#[CoversClass(BooleanType::class)]
#[Small]
final class TypeTest extends TestCase
{
    public function testCanBeInteger(): void
    {
        $type = Type::integer();

        $this->assertTrue($type->isInteger());
        $this->assertFalse($type->isFloat());
        $this->assertFalse($type->isString());
        $this->assertFalse($type->isBoolean());
    }

    public function testCanBeFloat(): void
    {
        $type = Type::float();

        $this->assertTrue($type->isFloat());
        $this->assertFalse($type->isInteger());
        $this->assertFalse($type->isString());
        $this->assertFalse($type->isBoolean());
    }

    public function testCanBeString(): void
    {
        $type = Type::string();

        $this->assertTrue($type->isString());
        $this->assertFalse($type->isInteger());
        $this->assertFalse($type->isFloat());
        $this->assertFalse($type->isBoolean());
    }

    public function testCanBeBoolean(): void
    {
        $type = Type::boolean();

        $this->assertTrue($type->isBoolean());
        $this->assertFalse($type->isString());
        $this->assertFalse($type->isInteger());
        $this->assertFalse($type->isFloat());
    }
}
