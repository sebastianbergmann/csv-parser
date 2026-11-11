[![License](https://img.shields.io/github/license/sanmai/duoclock.svg)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/sanmai/duoclock.svg)](https://packagist.org/packages/sanmai/duoclock)

# DuoClock

DuoClock is a PSR-20-compatible clock abstraction. It provides dual time access (`DateTimeImmutable`, `int`, `float`) and mockable sleep functions (`sleep`, `usleep`, and more) for testing time-sensitive code.

## Features

* Implements `Psr\Clock\ClockInterface`.
* Provides:
  * `now(): DateTimeImmutable`
  * `time(): int`
  * `microtime(): float`
  * `hrtime(): int` (monotonic nanoseconds)
* Offers mockable `sleep()`, `usleep()`, `nanosleep()`, and `time_nanosleep()` for test environments.
* Provides `getStartTick()` and `getEndTick()` for measuring elapsed time.
* Mockable time methods: `now()`, `time()`, `microtime()`, and `hrtime()`.
* Includes a deterministic `TimeSpy` for testing.
* Is minimal, with a lightweight design (depends only on `psr/clock`).
* Has all classes non-final to allow easy mocking and testing.

## Installation

```bash
composer require sanmai/duoclock
```

## Interfaces

```php
namespace DuoClock;

interface DuoClockInterface
{
    public function time(): int;
    public function microtime(): float;
}

interface SleeperInterface
{
    public function sleep(int $seconds): int;
    public function usleep(int $microseconds): void;
}

interface NanoSleeperInterface
{
    public function time_nanosleep(int $seconds, int $nanoseconds): array|bool;
    public function nanosleep(int $nanoseconds): array|bool;
}

interface TickerInterface
{
    public function getStartTick(): float;
    public function getEndTick(): float;
}

interface MonotonicClockInterface
{
    public function hrtime(): int;
}
```


## Usage

Real Clock:

```php
$clock = new DuoClock\DuoClock();

$clock->now();        // DateTimeImmutable
$clock->time();       // int
$clock->microtime();  // float
$clock->hrtime();     // int, nanoseconds, same as hrtime(true)

$clock->sleep(1);     // real sleep
$clock->usleep(1000); // real micro-sleep

$clock->nanosleep(1_500_000_000); // sleep 1.5 seconds
$clock->time_nanosleep(1, 500_000_000); // same as above
```

### Measuring Elapsed Time

```php
$clock = new DuoClock\DuoClock();

$timer = $clock->getStartTick();
// ...work...
$timer += $clock->getEndTick();
// $timer now contains elapsed seconds as float
```

TimeSpy, as a testing-time dependency:

```php
$clock = new DuoClock\TimeSpy(1752321600); // Corresponds to '2025-07-12T12:00:00Z'

$clock->time();       // 1752321600

$clock->sleep(10);    // advances virtual clock by 10 seconds
$clock->usleep(5000); // advances virtual clock by 0.005 seconds

$clock->time();       // 1752321610
$clock->microtime();  // 1752321610.005
$clock->hrtime();     // 10005000000, starts at zero, setTime() does not change it
```

### Mocking and Spies

The recommended approach is to always use TimeSpy for testing (`$clock = new TimeSpy();`) because calls to `$clock->sleep()` and `$clock->usleep()` do not delay execution even if you do not specifically mock them.

```php
$mock = $this->createMock(DuoClock\TimeSpy::class);

$mock->expects($this->exactly(1))
    ->method('time')
    ->willReturn(self::TIME_BEFORE_LAUNCH);

$example = new ExampleUsingTime($mock);
$this->assertFalse($example->launch());
```

```php
$mock = $this->createMock(DuoClock\TimeSpy::class);

$mock->expects($this->exactly(1))
    ->method('usleep')
    ->with(self::POLL_TIME);

$example = new ExampleUsingSleep($mock);
$example->waitDuringPolling();
```

## Why DuoClock Exists

PHP now has [PSR-20](https://www.php-fig.org/psr/psr-20/), a standard interface for representing the current time using immutable objects. This interface works well for many applications, but assumes that all time-based code should consume `DateTimeImmutable`. In practice, testing time-based code often requires mocking and emulating `sleep()` and `usleep()`, especially for retry logic, timeout simulations, or rate limiters. You do not want to wait for literal seconds for your `sleep()` tests to pass! PSR-20 offers no solution for this, which is where DuoClock steps in.

## Development

```bash
# Run all checks (tests, static analysis, mutation testing)
make -j -k
```

## License

Licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for details.
