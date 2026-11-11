<?php

/**
 * Copyright 2025 Alexey Kopytko <alexey@kopytko.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace DuoClock;

use DateTimeImmutable;
use DuoClock\Interfaces\DuoClockInterface;
use DuoClock\Interfaces\MonotonicClockInterface;
use DuoClock\Interfaces\NanoSleeperInterface;
use DuoClock\Interfaces\SleeperInterface;
use DuoClock\Interfaces\TickerInterface;
use Override;
use Psr\Clock\ClockInterface;

use function hrtime;
use function intdiv;
use function microtime;
use function sleep;
use function time;
use function time_nanosleep;
use function usleep;

class DuoClock implements SleeperInterface, NanoSleeperInterface, TickerInterface, ClockInterface, DuoClockInterface, MonotonicClockInterface
{
    protected const NANOSECONDS_PER_SECOND = 1_000_000_000;

    #[Override]
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    #[Override]
    public function time(): int
    {
        return time();
    }

    #[Override]
    public function microtime(): float
    {
        return microtime(true);
    }

    #[Override]
    public function hrtime(): int
    {
        return hrtime(true);
    }

    #[Override]
    public function sleep(int $seconds): int
    {
        // @infection-ignore-all
        return sleep($seconds);
    }

    #[Override]
    public function usleep(int $microseconds): void
    {
        // @infection-ignore-all
        usleep($microseconds);
    }

    #[Override]
    public function time_nanosleep(int $seconds, int $nanoseconds): array|bool
    {
        // @infection-ignore-all
        return time_nanosleep($seconds, $nanoseconds);
    }

    #[Override]
    public function nanosleep(int $nanoseconds): array|bool
    {
        /** @var non-negative-int */
        $seconds = intdiv($nanoseconds, self::NANOSECONDS_PER_SECOND);

        return $this->time_nanosleep($seconds, $nanoseconds % self::NANOSECONDS_PER_SECOND);
    }

    #[Override]
    public function getStartTick(): float
    {
        return -$this->microtime();
    }

    #[Override]
    public function getEndTick(): float
    {
        return $this->microtime();
    }
}
