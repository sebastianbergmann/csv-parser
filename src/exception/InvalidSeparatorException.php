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

use InvalidArgumentException;

final class InvalidSeparatorException extends InvalidArgumentException implements Exception
{
    public function __construct()
    {
        parent::__construct('Separator must be a one single-byte character');
    }
}
