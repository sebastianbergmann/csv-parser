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

namespace DuoClock\Interfaces;

interface NanoSleeperInterface
{
    /**
     * @param non-negative-int $seconds
     * @param non-negative-int $nanoseconds
     *
     * @return array{seconds: non-negative-int, nanoseconds: non-negative-int}|bool
     */
    public function time_nanosleep(int $seconds, int $nanoseconds): array|bool;

    /**
     * @param non-negative-int $nanoseconds
     *
     * @return array{seconds: non-negative-int, nanoseconds: non-negative-int}|bool
     */
    public function nanosleep(int $nanoseconds): array|bool;
}
