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

use const PHP_INT_MAX;
use const PHP_INT_MIN;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Type::class)]
#[CoversClass(BooleanType::class)]
#[CoversClass(IntegerType::class)]
#[CoversClass(FloatType::class)]
#[CoversClass(ObjectType::class)]
#[CoversClass(StringType::class)]
#[Small]
final class TypeTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function invalidIntegerProvider(): array
    {
        return [
            ['abc', '"abc" is not a numeric value'],
            ['01abc', '"01abc" is not a numeric value'],
            ['1.1', '"1.1" cannot be represented as an integer'],
            ['9223372036854775808', '"9223372036854775808" is out of range for an integer'],
            ['-9223372036854775809', '"-9223372036854775809" is out of range for an integer'],
            ['9.223372036854775808E+18', '"9.223372036854775808E+18" cannot be represented as an integer'],
            ['1e20', '"1e20" cannot be represented as an integer'],
        ];
    }

    public function testCanConvertStringToBoolean(): void
    {
        $this->assertSame(true, Type::boolean()->apply('1'));
        $this->assertSame(false, Type::boolean()->apply('0'));
    }

    public function testCanConvertStringToInteger(): void
    {
        $this->assertSame(1, Type::integer()->apply('1'));
        $this->assertSame(1, Type::integer()->apply('01'));
        $this->assertSame(-1, Type::integer()->apply('-01'));
        $this->assertSame(1, Type::integer()->apply('1.0'));
        $this->assertSame(PHP_INT_MAX, Type::integer()->apply((string) PHP_INT_MAX));
        $this->assertSame(PHP_INT_MIN, Type::integer()->apply((string) PHP_INT_MIN));
        $this->assertSame(PHP_INT_MIN, Type::integer()->apply('-9.223372036854775808E+18'));
    }

    #[DataProvider('invalidIntegerProvider')]
    public function testCannotConvertInvalidStringToInteger(string $input, string $message): void
    {
        $this->expectException(InvalidIntegerException::class);
        $this->expectExceptionMessage($message);

        Type::integer()->apply($input);
    }

    public function testCanConvertStringToFloat(): void
    {
        $this->assertSame(1.0, Type::float()->apply('1.0'));
    }

    public function testCanConvertStringToObjectUsingObjectMapper(): void
    {
        $callback = new class implements ObjectMapper
        {
            public function map(string $value): DateTimeImmutable
            {
                return new DateTimeImmutable($value);
            }
        };

        $object = Type::object($callback)->apply('2023-03-24');

        $this->assertInstanceOf(DateTimeImmutable::class, $object);
        $this->assertSame('2023-03-24', $object->format('Y-m-d'));
    }

    public function testKeepsStringsAsTheyAre(): void
    {
        $this->assertSame('1', Type::string()->apply('1'));
    }
}
