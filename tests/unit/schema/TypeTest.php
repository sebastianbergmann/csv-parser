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
    public function testCanCastStringToBoolean(): void
    {
        $this->assertSame(true, Type::boolean()->apply('1'));
        $this->assertSame(false, Type::boolean()->apply('0'));
    }

    public function testCanCastStringToInteger(): void
    {
        $this->assertSame(1, Type::integer()->apply('1'));
    }

    public function testCanCastStringToFloat(): void
    {
        $this->assertSame(1.0, Type::float()->apply('1.0'));
    }

    public function testCanCastStringToString(): void
    {
        $this->assertSame('1', Type::string()->apply('1'));
    }
}
