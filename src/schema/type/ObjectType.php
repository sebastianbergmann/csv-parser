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

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for this library
 */
final class ObjectType extends Type
{
    private ObjectMapper $mapper;

    /** @noinspection PhpMissingParentConstructorInspection */
    protected function __construct(ObjectMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function apply(string $value): object
    {
        return $this->mapper->map($value);
    }
}
