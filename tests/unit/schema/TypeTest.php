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

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Type::class)]
#[CoversClass(BooleanType::class)]
#[CoversClass(CallbackType::class)]
#[CoversClass(IntegerType::class)]
#[CoversClass(FloatType::class)]
#[CoversClass(StringType::class)]
#[Small]
final class TypeTest extends TestCase
{
    public function testCanConvertStringToBoolean(): void
    {
        $this->assertSame(true, Type::boolean()->apply('1'));
        $this->assertSame(false, Type::boolean()->apply('0'));
    }

    public function testCanConvertStringToInteger(): void
    {
        $this->assertSame(1, Type::integer()->apply('1'));
    }

    public function testCanConvertStringToFloat(): void
    {
        $this->assertSame(1.0, Type::float()->apply('1.0'));
    }

    public function testKeepsStringsAsTheyAre(): void
    {
        $this->assertSame('1', Type::string()->apply('1'));
    }

    public function testCanConvertStringUsingCallback(): void
    {
        $callback = new class implements Callback
        {
            public function apply(string $value): DateTimeImmutable
            {
                return new DateTimeImmutable($value);
            }
        };

        $this->assertSame('2023-03-24', Type::callback($callback)->apply('2023-03-24')->format('Y-m-d'));
    }
}
