<?php

/**
 * Copyright (c) 2017, Maks Rafalko
 * Copyright (c) 2020, Théo FIDRY
 * Copyright (c) 2025, Alexey Kopytko
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

declare(strict_types=1);

namespace DIContainer;

use IteratorAggregate;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Traversable;
use Generator;

use function array_key_exists;
use function count;
use function get_class;
use function is_a;
use function is_callable;
use function Pipeline\take;
use function reset;
use function sprintf;
use function str_contains;
use function class_exists;
use function interface_exists;
use function in_array;

/**
 * @implements IteratorAggregate<array-key, class-string<object>|non-empty-string>
 */
class Container implements ContainerInterface, IteratorAggregate
{
    /**
     * @var array<class-string<object>|non-empty-string, object>
     */
    private array $values = [];

    /**
     * @var array<class-string<object>|non-empty-string, object>
     */
    private array $prebuilt = [];

    /**
     * @var array<class-string<object>|non-empty-string, callable>
     */
    private array $factories = [];

    /**
     * @var array<class-string<object>|non-empty-string, class-string<Builder<object>>>
     */
    private array $builders = [];

    /**
     * @var array<class-string<object>|non-empty-string, class-string<object>>
     */
    private array $implementations = [];

    /** Placeholder marking an unresolved parameter */
    private readonly object $missing;

    /** Placeholder marking a parameter for PHP to use its default value */
    private readonly object $omit;

    /**
     * @param iterable<class-string<object>, callable|class-string<Builder<object>>|class-string<object>> $values
     * @param iterable<non-empty-string, callable|class-string<Builder<object>>|class-string<object>> $bindings For non-class service IDs
     */
    public function __construct(iterable $values = [], iterable $bindings = [])
    {
        // Cache the value letting a builder override it
        $this->values[ContainerInterface::class] = $this;
        $this->implementations[ContainerInterface::class] = ContainerInterface::class;

        $this->missing = new class {};
        $this->omit = new class {};

        foreach ($values as $id => $value) {
            $this->set($id, $value);
        }

        foreach ($bindings as $id => $binding) {
            $this->bind($id, $binding);
        }
    }

    /**
     * Validates that a cached $this set in the constructor still a thing.
     * If the implementation has an impossible mapping interface to interface,
     * and the cache has an entry, it can only be one thing that we set ourselves.
     */
    private function hasCachedSelf(): bool
    {
        if (!array_key_exists(ContainerInterface::class, $this->implementations)) {
            return false;
        }

        if (ContainerInterface::class !== $this->implementations[ContainerInterface::class]) {
            return false;
        }

        return array_key_exists(ContainerInterface::class, $this->values);
    }

    public function __clone(): void
    {
        if (!$this->hasCachedSelf()) {
            return;
        }

        $this->values[ContainerInterface::class] = $this;
    }

    /**
     * Register a factory, a builder, or an implementation class for a class-based service ID.
     *
     * @template T of object
     * @param class-string<T> $id
     * @param class-string<T>|class-string<Builder<T>>|callable(static): T $value
     */
    public function set(string $id, callable|string $value): void
    {
        $this->bind($id, $value);
    }

    /**
     * Register a factory, a builder, or an implementation class for a non-class service ID (e.g., 'app.locator').
     *
     * @param non-empty-string $id
     * @param class-string<object>|class-string<Builder<object>>|callable(static): object $value
     */
    public function bind(string $id, callable|string $value): void
    {
        // Registered dependencies override everything else at bind time
        $this->remove($id);

        // A value can be a callable and also implement our `Builder` interface:
        // we must treat such cases as factories, not builders, to ensure
        // backward compatibility with the existing code that uses callables.
        if (is_callable($value)) {
            $this->factories[$id] = $value;
            return;
        }

        if (is_a($value, Builder::class, true)) {
            $this->builders[$id] = $value;
            return;
        }

        $this->implementations[$id] = $value;
    }

    /**
     * @param class-string<object>|non-empty-string $id
     */
    public function remove(string $id): void
    {
        unset(
            $this->values[$id],
            $this->factories[$id],
            $this->builders[$id],
            $this->implementations[$id],
            $this->prebuilt[$id],
        );
    }

    /**
     * Inject a pre-built object instance directly into the container.
     *
     * @template T of object
     * @param class-string<T>|non-empty-string $id
     * @param T $value
     */
    public function inject(string $id, object $value): void
    {
        self::assertType($id, $value);

        // Injected pre-built dependencies override everything else at the time of injection
        $this->remove($id);

        $this->prebuilt[$id] = $value;
    }

    /**
     * Stores a value, validating type for class-string IDs only.
     *
     * @template T of object
     *
     * @param class-string<T> $id accepts any string; non-class IDs skip validation
     * @phpstan-return T
     */
    private function setValueOrThrow(string $id, object $value): object
    {
        self::assertType($id, $value);

        $this->values[$id] = $value;

        /** @var T */
        return $value;
    }

    private static function assertType(string $id, object $value): void
    {
        // Break the contract to skip the type check for IDs that do not look like a valid namespaced PHP class name
        if (str_contains($id, '.') || !str_contains($id, '\\')) {
            return;
        }

        if (!$value instanceof $id) {
            throw new Exception(sprintf('Expected instance of %s, got %s', $id, get_class($value)));
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id)
    {
        // All kinds of already built instances come first
        if (array_key_exists($id, $this->values)) {
            return $this->values[$id];
        }

        if (array_key_exists($id, $this->prebuilt)) {
            return $this->prebuilt[$id];
        }

        // Resolve concrete implementations using common rules
        if (
            array_key_exists($id, $this->implementations)
            && $this->implementations[$id] !== $id
        ) {
            return $this->setValueOrThrow($id, $this->get($this->implementations[$id]));
        }

        // Builders and factories assume extra work, so they follow next
        if (array_key_exists($id, $this->builders)) {
            /** @var Builder<T> $builder */
            $builder = $this->get($this->builders[$id]);

            return $this->setValueOrThrow($id, $builder->build());
        }

        if (array_key_exists($id, $this->factories)) {
            /** @var T $value */
            $value = $this->factories[$id]($this);

            return $this->setValueOrThrow($id, $value);
        }

        $value = $this->createService($id);

        if (null === $value) {
            throw new Exception(sprintf('Unknown service "%s"', $id));
        }

        return $this->setValueOrThrow($id, $value);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     * @phpstan-return ?T
     */
    private function createService(string $id): ?object
    {
        $reflectionClass = new ReflectionClass($id);
        $constructor = $reflectionClass->getConstructor();

        if (!$reflectionClass->isInstantiable()) {
            return null;
        }

        if (null === $constructor || 0 === $constructor->getNumberOfParameters()) {
            return $reflectionClass->newInstance();
        }

        $resolvedArguments = take($constructor->getParameters())
            ->map($this->parameterNameToValue(...))
            ->select($this->notOmitted(...))
            ->toAssoc();

        // Check if we identified all parameters for the service
        if (in_array($this->missing, $resolvedArguments, true)) {
            return null;
        }

        return $reflectionClass->newInstanceArgs($resolvedArguments);
    }

    /**
     * Named arguments enable PHP to evaluate the defaults of the omitted parameters.
     * @return Generator<string, mixed>
     */
    private function parameterNameToValue(ReflectionParameter $parameter): Generator
    {
        yield $parameter->getName() => $this->findParameterValue($parameter);
    }

    private function notOmitted(mixed $value): bool
    {
        return $this->omit !== $value;
    }

    private function deferToDefault(ReflectionParameter $parameter): object
    {
        return match ($parameter->isOptional()) {
            true => $this->omit,
            default => $this->missing,
        };
    }

    /**
     * Returns a possible argument value for the constructor; it can return a specific computed
     * value just as well as a placeholder for an omitted or a missing value.
     *
     * @return mixed
     */
    private function findParameterValue(ReflectionParameter $parameter): mixed
    {
        // Variadic parameters imply collections, which we do not provide, for now
        if ($parameter->isVariadic()) {
            return $this->omit;
        }

        $paramType = $parameter->getType();

        // Not considering composite types, such as unions or intersections, for now
        if (!$paramType instanceof ReflectionNamedType) {
            return $this->deferToDefault($parameter);
        }

        /** @var class-string $paramTypeName */
        $paramTypeName = $paramType->getName();

        // Defer to a default value for built-in types and classes that cannot be reflected
        if (!class_exists($paramTypeName) && !interface_exists($paramTypeName)) {
            return $this->deferToDefault($parameter);
        }

        // Found an instantiable class, done
        if ((new ReflectionClass($paramTypeName))->isInstantiable()) {
            $default = $this->objectDefault($parameter, $paramTypeName);

            // Make sure the null-default parameters get autowired for BC reasons
            if (null !== $default) {
                return $default;
            }

            return $this->get($paramTypeName);
        }

        // Look for a factory that can create an instance of an interface or abstract class
        $matchingTypes = $this->providersForType($paramTypeName);

        // We expect exactly one factory to match the type to resolve a parameter unambiguously
        if (1 === count($matchingTypes)) {
            return $this->get(reset($matchingTypes));
        }

        // But we should also consider default values if present and no default provider
        if ([] === $matchingTypes) {
            return $this->deferToDefault($parameter);
        }

        return $this->missing;
    }

    /**
     * Returns the default value unless the type is registered.
     */
    private function objectDefault(ReflectionParameter $parameter, string $paramTypeName): ?object
    {
        if (!$parameter->isOptional()) {
            return null;
        }

        if ($this->isRegistered($paramTypeName)) {
            return null;
        }

        /** @var ?object */
        return $parameter->getDefaultValue();
    }

    /**
     * Retrieves the class or interface names of all registered factories/builders/etc that can produce instances of the given type.
     * This includes direct implementations, subclasses, or the type itself.
     *
     * @template T of object
     * @param class-string<T> $type the class or interface name to find factories for
     * @return list<class-string<object>> a list of factory IDs (class-strings) that are compatible with the given type
     */
    private function providersForType(string $type): array
    {
        /** @var list<class-string<object>> */
        return take($this->factories, $this->builders, $this->implementations, $this->prebuilt)
            ->keys()
            ->filter(static fn(string $id) => is_a($id, $type, true))
            ->toList();
    }

    public function has(string $id): bool
    {
        if (array_key_exists($id, $this->values)) {
            return true;
        }

        return $this->isRegistered($id);
    }

    private function isRegistered(string $id): bool
    {
        if (array_key_exists($id, $this->implementations)) {
            return true;
        }

        if (array_key_exists($id, $this->prebuilt)) {
            return true;
        }

        if (array_key_exists($id, $this->factories)) {
            return true;
        }

        if (array_key_exists($id, $this->builders)) {
            return true;
        }

        // Very pessimistic; could probably try to create it.
        return false;
    }

    public function getIterator(): Traversable
    {
        return take(
            $this->factories,
            $this->builders,
            $this->implementations,
            $this->prebuilt,
        )->keys();
    }
}
