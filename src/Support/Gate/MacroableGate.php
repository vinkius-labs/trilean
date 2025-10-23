<?php

namespace VinkiusLabs\Trilean\Support\Gate;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Gate as BaseGate;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use function config;
use function method_exists;
use function str_contains;
use function ternary;

class MacroableGate extends BaseGate
{
    public static function fromGate(BaseGate $gate): self
    {
        if ($gate instanceof self) {
            return $gate;
        }

        $reflection = new ReflectionClass(BaseGate::class);

        $properties = [
            'container',
            'userResolver',
            'abilities',
            'policies',
            'beforeCallbacks',
            'afterCallbacks',
            'guessPolicyNamesUsingCallback',
            'stringCallbacks',
            'defaultDenialResponse',
        ];

        $values = [];

        foreach ($properties as $property) {
            $propertyRef = $reflection->getProperty($property);
            $propertyRef->setAccessible(true);
            $values[$property] = $propertyRef->getValue($gate);
        }

        $instance = new self(
            $values['container'],
            $values['userResolver'],
            $values['abilities'],
            $values['policies'],
            $values['beforeCallbacks'],
            $values['afterCallbacks'],
            $values['guessPolicyNamesUsingCallback'],
        );

        foreach (['stringCallbacks', 'defaultDenialResponse'] as $property) {
            $propertyRef = $reflection->getProperty($property);
            $propertyRef->setAccessible(true);
            $propertyRef->setValue($instance, $values[$property]);
        }

        return $instance;
    }

    public function defineTernary(string $ability, callable $callback, array $options = []): static
    {
        $config = config('trilean.policies');

        $this->define($ability, function ($user = null, ...$arguments) use ($callback, $options, $config) {
            $state = ternary($this->invokeTernaryCallback($callback, $user, $arguments));

            if ($state->isUnknown()) {
                if (($options['throw'] ?? $config['throw_on_unknown']) === true) {
                    throw new AuthorizationException($options['message'] ?? $config['unknown_message']);
                }

                return $options['fallback'] ?? $config['unknown_resolves_to'];
            }

            return $state->isTrue();
        });

        return $this;
    }

    public function inspectTernary(string $ability, ?array $arguments = null): array
    {
        $arguments ??= [];
        $response = $this->inspect($ability, $arguments);

        $metadata = method_exists($response, 'context') ? $response->context() : [];

        return [
            'allowed' => $response->allowed(),
            'denied' => $response->denied(),
            'metadata' => $metadata,
        ];
    }

    /**
     * Invoke the underlying callback while respecting its declared signature.
     */
    protected function invokeTernaryCallback(callable $callback, $user, array $arguments): mixed
    {
        $reflection = $this->reflectCallback($callback);

        $argumentLimit = $reflection->isVariadic()
            ? PHP_INT_MAX
            : $reflection->getNumberOfParameters();

        $payload = array_slice([$user, ...$arguments], 0, $argumentLimit);

        return $callback(...$payload);
    }

    /**
     * Get a reflection instance for the provided callback.
     */
    protected function reflectCallback(callable $callback): ReflectionFunctionAbstract
    {
        if (is_array($callback)) {
            return new ReflectionMethod($callback[0], $callback[1]);
        }

        if (is_string($callback) && str_contains($callback, '::')) {
            return new ReflectionMethod($callback);
        }

        if (is_object($callback) && ! $callback instanceof \Closure) {
            return new ReflectionMethod($callback, '__invoke');
        }

        return new ReflectionFunction($callback);
    }
}
