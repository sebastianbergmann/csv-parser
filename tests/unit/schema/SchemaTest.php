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
#[UsesClass(FieldDefinition::class)]
#[UsesClass(Type::class)]
#[UsesClass(BooleanType::class)]
#[UsesClass(IntegerType::class)]
#[UsesClass(FloatType::class)]
#[UsesClass(StringType::class)]
#[Small]
final class SchemaTest extends TestCase
{
    public function testAppliesFieldDefinitionsToMapInputArrayToOutputArray(): void
    {
        $schema = Schema::from(
            FieldDefinition::from(1, 'a', Type::integer()),
            FieldDefinition::from(2, 'b', Type::float()),
            FieldDefinition::from(3, 'c', Type::string()),
            FieldDefinition::from(4, 'd', Type::boolean()),
        );

        $this->assertSame(
            [
                'a' => 1,
                'b' => 1.1,
                'c' => '1',
                'd' => true,
            ],
            $schema->apply(
                [
                    '1',
                    '1.1',
                    '1',
                    '1',
                ],
            ),
        );
    }
}
