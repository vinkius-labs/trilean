<?php

namespace VinkiusLabs\Trilean\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use VinkiusLabs\Trilean\Enums\TernaryState;
use function ternary;

class GateMacros
{
    public static function register(): void
    {
        if (Gate::hasMacro('defineTernary')) {
            return;
        }

        Gate::macro('defineTernary', function (string $ability, callable $callback, array $options = []) {
            $config = config('trilean.policies');

            Gate::define($ability, function ($user, ...$arguments) use ($callback, $options, $config) {
                $state = ternary($callback($user, ...$arguments));

                if ($state->isUnknown()) {
                    if (($options['throw'] ?? $config['throw_on_unknown']) === true) {
                        throw new AuthorizationException($options['message'] ?? $config['unknown_message']);
                    }

                    return $options['fallback'] ?? $config['unknown_resolves_to'];
                }

                return $state->isTrue();
            });
        });

        Gate::macro('inspectTernary', function (string $ability, ?array $arguments = null) {
            $arguments ??= [];
            $response = Gate::inspect($ability, $arguments);

            $metadata = method_exists($response, 'context') ? $response->context() : [];

            return [
                'allowed' => $response->allowed(),
                'denied' => $response->denied(),
                'metadata' => $metadata,
            ];
        });
    }
}
