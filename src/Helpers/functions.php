<?php

use VinkiusLabs\Trilean\Enums\TernaryState;

/**
 * Global Helper Functions for Trilean Package
 * 
 * Key principles:
 * 1. Single way to do common things
 * 2. Obvious syntax without magic
 * 3. Minimal typing required
 * 4. Consistent return patterns
 */

if (!function_exists('is_true')) {
    /**
     * Direct boolean check - no intermediate objects.
     * 
     * @example if (is_true($user->verified)) { }
     */
    function is_true(mixed $value): bool
    {
        return TernaryState::fromMixed($value)->isTrue();
    }
}

if (!function_exists('is_false')) {
    /**
     * Direct boolean check for false state.
     * 
     * @example if (is_false($user->blocked)) { }
     */
    function is_false(mixed $value): bool
    {
        return TernaryState::fromMixed($value)->isFalse();
    }
}

if (!function_exists('is_unknown')) {
    /**
     * Direct boolean check for unknown state.
     * 
     * @example if (is_unknown($user->consent)) { }
     */
    function is_unknown(mixed $value): bool
    {
        return TernaryState::fromMixed($value)->isUnknown();
    }
}

if (!function_exists('and_all')) {
    /**
     * Simple AND operation - returns boolean directly.
     * 
     * @example if (and_all($verified, $consented, $active)) { }
     */
    function and_all(mixed ...$values): bool
    {
        foreach ($values as $value) {
            $state = TernaryState::fromMixed($value);
            if ($state->isFalse()) return false;
            if ($state->isUnknown()) return false;
        }
        return true;
    }
}

if (!function_exists('or_any')) {
    /**
     * Simple OR operation - returns boolean directly.
     * 
     * @example if (or_any($method1, $method2, $method3)) { }
     */
    function or_any(mixed ...$values): bool
    {
        foreach ($values as $value) {
            if (is_true($value)) return true;
        }
        return false;
    }
}

if (!function_exists('pick')) {
    /**
     * Simplified conditional - most common use case.
     * 
     * @example echo pick($user->active, 'Active', 'Inactive');
     * @example echo pick($status, 'Yes', 'No', 'Maybe');
     */
    function pick(mixed $condition, mixed $ifTrue, mixed $ifFalse, mixed $ifUnknown = null): mixed
    {
        $state = TernaryState::fromMixed($condition);

        return match ($state) {
            TernaryState::TRUE => $ifTrue,
            TernaryState::FALSE => $ifFalse,
            TernaryState::UNKNOWN => $ifUnknown ?? $ifFalse,
        };
    }
}

if (!function_exists('when_true')) {
    /**
     * Execute callback only when true - common pattern.
     * 
     * @example when_true($user->verified, fn() => $user->activate());
     */
    function when_true(mixed $condition, callable $callback): mixed
    {
        return is_true($condition) ? $callback() : null;
    }
}

if (!function_exists('when_false')) {
    /**
     * Execute callback only when false.
     * 
     * @example when_false($user->blocked, fn() => $user->sendWelcomeEmail());
     */
    function when_false(mixed $condition, callable $callback): mixed
    {
        return is_false($condition) ? $callback() : null;
    }
}

if (!function_exists('when_unknown')) {
    /**
     * Execute callback only when unknown.
     * 
     * @example when_unknown($user->consent, fn() => $user->requestConsent());
     */
    function when_unknown(mixed $condition, callable $callback): mixed
    {
        return is_unknown($condition) ? $callback() : null;
    }
}

if (!function_exists('vote')) {
    /**
     * Simple majority decision - easier than consensus().
     * 
     * @example $result = vote($check1, $check2, $check3); // returns 'true', 'false', or 'tie'
     */
    function vote(mixed ...$values): string
    {
        $true_count = 0;
        $false_count = 0;
        $unknown_count = 0;

        foreach ($values as $value) {
            $state = TernaryState::fromMixed($value);
            match ($state) {
                TernaryState::TRUE => $true_count++,
                TernaryState::FALSE => $false_count++,
                TernaryState::UNKNOWN => $unknown_count++,
            };
        }

        if ($true_count > $false_count && $true_count > $unknown_count) {
            return 'true';
        }

        if ($false_count > $true_count && $false_count > $unknown_count) {
            return 'false';
        }

        return 'tie';
    }
}

if (!function_exists('safe_bool')) {
    /**
     * Convert to boolean with explicit unknown handling.
     * 
     * @example $active = safe_bool($user->active, default: false);
     */
    function safe_bool(mixed $value, bool $default = false): bool
    {
        $state = TernaryState::fromMixed($value);

        return match ($state) {
            TernaryState::TRUE => true,
            TernaryState::FALSE => false,
            TernaryState::UNKNOWN => $default,
        };
    }
}

if (!function_exists('require_true')) {
    /**
     * Throw exception if not true - useful for validation.
     * 
     * @example require_true($user->verified, 'User must be verified');
     */
    function require_true(mixed $value, string $message = 'Value must be true'): void
    {
        if (!is_true($value)) {
            throw new \InvalidArgumentException($message);
        }
    }
}

if (!function_exists('require_not_false')) {
    /**
     * Throw exception if false - allows true or unknown.
     * 
     * @example require_not_false($user->blocked, 'User is blocked');
     */
    function require_not_false(mixed $value, string $message = 'Value cannot be false'): void
    {
        if (is_false($value)) {
            throw new \InvalidArgumentException($message);
        }
    }
}
