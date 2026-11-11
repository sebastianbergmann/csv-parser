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
use Override;

use function intdiv;

class TimeSpy extends DuoClock
{
    /**
     * @var int Current unix time to mimic (int timestamp)
     */
    private int $time = 0;

    /**
     * @var float Current unix time to mimic (as a float timestamp)
     */
    private float $microtime = 0.0;

    /**
     * @var int Monotonic clock value in nanoseconds; setTime() does not change it
     */
    private int $hrtime = 0;

    public function __construct(float|int $time = 0.0)
    {
        $this->setTime($time);
    }

    public function setTime(float|int $time): void
    {
        $this->time = (int) $time;
        $this->microtime = $time;
    }

    #[Override]
    public function now(): DateTimeImmutable
    {
        // we do not preserve the microseconds here to keep things simple
        return new DateTimeImmutable("@{$this->time}");
    }

    #[Override]
    public function time(): int
    {
        return $this->time;
    }

    #[Override]
    public function microtime(): float
    {
        return $this->microtime;
    }

    #[Override]
    public function hrtime(): int
    {
        return $this->hrtime;
    }

    #[Override]
    public function sleep(int $seconds): int
    {
        // Advance the frozen time by the specified number of seconds
        $this->time += $seconds;
        $this->microtime += $seconds;
        $this->hrtime += $seconds * self::NANOSECONDS_PER_SECOND;

        return 0;
    }

    #[Override]
    public function usleep(int $microseconds): void
    {
        // Add microseconds to microtime
        $this->microtime += $microseconds / 1_000_000.0;

        // Update integer time to match
        $this->time = (int) $this->microtime;

        $this->hrtime += $microseconds * 1_000;
    }

    #[Override]
    public function time_nanosleep(int $seconds, int $nanoseconds): array|bool
    {
        $this->microtime += $seconds + $nanoseconds / self::NANOSECONDS_PER_SECOND;
        $this->time = (int) $this->microtime;
        $this->hrtime += $seconds * self::NANOSECONDS_PER_SECOND + $nanoseconds;

        return true;
    }

    // @infection-ignore-all
    #[Override]
    public function nanosleep(int $nanoseconds): array|bool
    {
        /** @var non-negative-int */
        $seconds = intdiv($nanoseconds, self::NANOSECONDS_PER_SECOND);

        return $this->time_nanosleep($seconds, $nanoseconds % self::NANOSECONDS_PER_SECOND);
    }
}
