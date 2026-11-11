# sanmai/di-container

A straightforward PSR-11 dependency injection container with automatic constructor dependency resolution.

I designed the autowiring [initially for Infection's own use](https://github.com/infection/infection/pull/2118) with focus on simplicity and zero configuration, building on the existing implementation by [Maks Rafalko](https://github.com/maks-rafalko) and [Théo Fidry](https://github.com/theofidry).

## Installation

```bash
composer require sanmai/di-container
```

## Features

- Automatically resolves class dependencies through reflection
- Objects are created once and reused
- Resolve interfaces to concrete implementations

## Quick Start

```php
use DIContainer\Container;

$container = new Container();

// Automatic resolution - no configuration needed
$service = $container->get(YourService::class);

// Use builder objects for complex construction, or construct the dependencies directly - your choice
$container = new Container([
    ComplexObject::class => fn(Container $container) => new ComplexObject(
        $container->get(LoggerInterface::class),
        $container->get(AnotherProvider::class)->getValue()
    ),
    DatabaseInterface::class => fn(Container $container) =>
        $container->get(DatabaseBuilder::class)->build(),
    // Concrete class names also work when suggesting a preferred implementation
    CommonAbstractClass::class => ConcreteImplementation::class,
]);

// Set additional dependencies on the fly
$container->set(LoggerInterface::class, fn() => new FileLogger('debug.log'));
$container->set(SomeInterface::class, PreferredImplementation::class);

$service = $container->get(ServiceNeedingDatabase::class); // Auto-injects database
```

The order in which you define your services is not important, as dependencies are only resolved when they are requested.

That said, injected pre-built instances override prior dependencies for that ID, but a later `set()` or `bind()` overrides again.

## Builder Objects

Builder objects can encapsulate arbitrary complex construction logic. They can use dependency injection, which makes them cohesive, independently testable, and reusable.

```php
use DIContainer\Builder;

/**
 * Builder that accepts injectable dependencies.
 *
 * @implements Builder<DatabaseInterface>
 */
class DatabaseBuilder implements Builder
{
    public function __construct(
        private readonly ConfigProvider $config,
        private readonly Logger $logger
    ) {}

    public function build(): DatabaseInterface
    {
        return new MySQLDatabase(
            $this->config->getDatabaseHost(),
            $this->config->getDatabaseCredentials(),
            $this->logger
        );
    }
}
```

The `Builder` interface uses a covariant template (`@template-covariant T`), so PHPStan correctly validates that your builder's return type matches the declared template parameter.

When you implement the `Builder` interface, you can simply provide the builder class name instead of a closure. The container automatically detects builder classes and handles the instantiation and `build()` method call.

```php
// 1. Direct class name, if the class implements DIContainer\Builder interface
$container = new Container([
    DatabaseInterface::class => DatabaseBuilder::class,
]);

// 2. Explicit closure - what will the container do under the hood
$container = new Container([
    DatabaseInterface::class => fn(Container $container) => $container->get(DatabaseBuilder::class)->build(),
]);
```

For setting dependencies on the fly, there's a handy `set()` method that accepts callables, builder class names, and implementation class names. Callables have a priority over builders.

## Non-Class Service IDs

For non-class service IDs (e.g., `'app.repository'`), use the `bind()` method or the `$bindings` constructor parameter:

```php
// Register with a dotted ID - type validation is skipped
$container = new Container(
    values: [
        LoggerInterface::class => FileLogger::class,
    ],
    bindings: [
        'app.repository' => fn() => new CachedRepository(new DatabaseRepository()),
        'app.cache' => CacheBuilder::class,
    ]
);

// Or add bindings on the fly
$container->bind('app.mailer', fn() => new MyMailer());

// Builder classes work too
$container->bind('app.session', SessionBuilder::class);

$repository = $container->get('app.repository');
```

The `bind()` method and `$bindings` parameter accept callables, builder class names, and implementation class names, just like `set()`, but without class-string type constraints on the service ID.

## Pre-Built Instances

Use `inject()` to store objects that were created outside the container:

```php
$logger = new FileLogger('app.log');
$container->inject(LoggerInterface::class, $logger);
```

## Default Values

When a constructor gives a `new` expression as a default value, each consumer that the container builds gets its own object from that expression, with the arguments from the signature:

```php
public function __construct(
    private readonly SerializerInterface $serializer,
    private readonly RetryPolicy $policy = new RetryPolicy('fast', retries: 3),
) {}
```

The container does not share or cache this object. If you register `RetryPolicy`, the registration replaces the default value.

## Removing Services

Use `remove()` to forget a service together with any instances.

```php
$container->remove(LoggerInterface::class);

$container->has(LoggerInterface::class); // false
```

## Introspection

To inspect the registered service IDs, iterate over the container. Services created through autowiring are not included, and the order is unspecified.

```php
foreach ($container as $serviceId) {
    // ...
}
```

## Design Philosophy

This container prioritizes simplicity, predictability, and architectural purity. It achieves this through:

- Predictable autowiring; there are no complex background scans or fragile naming conventions.
- Lack of surprises; the container will only resolve an interface if it can find **exactly one** registered factory or builder that produces a compatible implementation. It will never guess, ensuring the dependency graph is always clear, just as your day is worry-free.
- Constructor-only dependency injection; the container intentionally avoids complex features, such as property/method injection or injection of variadic/composite types beyond default values. This approach promotes cleaner, more testable class designs.

The container resolves interfaces using a straightforward rule: when a dependency is an interface, it looks for exactly one registered factory or a builder that produces a compatible object.

This approach allows you to wire dependencies without explicitly linking an implementation to an interface; the container connects them logically as long as the relationship is unambiguous.

The container omits circular dependency checks for simplicity, an issue that even the most minimal automatic test will immediately reveal.

## Testing

```bash
make -j -k
```

## Benchmarking

```bash
make benchmark
```

Runs [PHPBench](https://phpbench.readthedocs.io/) with OPcache and JIT enabled.
