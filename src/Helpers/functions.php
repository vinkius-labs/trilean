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

if (!function_exists('ternary')) {
    /**
     * Convert any value to TernaryState.
     * 
     * @example ternary($value)->isTrue()
     * @example ternary($value)->ifTrue('Yes')->ifFalse('No')->resolve()
     */
    function ternary(mixed $value): \VinkiusLabs\Trilean\Enums\TernaryState
    {
        return \VinkiusLabs\Trilean\Enums\TernaryState::fromMixed($value);
    }
}

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

// ========================================
// Advanced Pattern Matching
// ========================================

if (!function_exists('match_ternary')) {
    /**
     * Advanced pattern matching with wildcard support.
     * 
     * @example 
     * match_ternary($status, [
     *     'premium|enterprise' => 'Advanced',
     *     'free|trial' => 'Basic',
     *     '*' => 'Unknown'
     * ])
     * 
     * @example
     * match_ternary([
     *     [is_true($verified), is_true($active)] => 'Ready',
     *     [is_true($verified)] => 'Pending',
     *     default => 'Blocked'
     * ])
     */
    function match_ternary(mixed $value, array $patterns = []): mixed
    {
        // Multi-condition matching (array of arrays)
        if (is_array($value) || isset($patterns[0]) && is_array($patterns[0])) {
            return \VinkiusLabs\Trilean\Support\TernaryPatternMatcher::matchConditions($value, $patterns);
        }

        // String-based wildcard matching
        return \VinkiusLabs\Trilean\Support\TernaryPatternMatcher::matchWildcard($value, $patterns);
    }
}

// ========================================
// Array Helpers (for non-Collection users)
// ========================================

if (!function_exists('array_all_true')) {
    /**
     * Check if all array values are true (ternary-aware).
     * 
     * @example array_all_true(['verified' => true, 'active' => 1, 'consent' => 'yes'])
     */
    function array_all_true(array $values): bool
    {
        return and_all(...array_values($values));
    }
}

if (!function_exists('array_any_true')) {
    /**
     * Check if any array value is true (ternary-aware).
     * 
     * @example array_any_true($checks)
     */
    function array_any_true(array $values): bool
    {
        return or_any(...array_values($values));
    }
}

if (!function_exists('array_filter_true')) {
    /**
     * Filter array keeping only true values (removes false/unknown).
     * 
     * @example array_filter_true($values)
     */
    function array_filter_true(array $values): array
    {
        return array_filter($values, fn($value) => is_true($value));
    }
}

if (!function_exists('array_filter_false')) {
    /**
     * Filter array keeping only false values.
     */
    function array_filter_false(array $values): array
    {
        return array_filter($values, fn($value) => is_false($value));
    }
}

if (!function_exists('array_filter_unknown')) {
    /**
     * Filter array keeping only unknown values.
     */
    function array_filter_unknown(array $values): array
    {
        return array_filter($values, fn($value) => is_unknown($value));
    }
}

if (!function_exists('array_count_ternary')) {
    /**
     * Count true/false/unknown values in array.
     * 
     * @example array_count_ternary($values) // ['true'=>3, 'false'=>1, 'unknown'=>2]
     */
    function array_count_ternary(array $values): array
    {
        $counts = ['true' => 0, 'false' => 0, 'unknown' => 0];

        foreach ($values as $value) {
            $state = \VinkiusLabs\Trilean\Enums\TernaryState::fromMixed($value);
            $counts[$state->value]++;
        }

        return $counts;
    }
}

// ========================================
// Ternary Coalescing
// ========================================

if (!function_exists('ternary_coalesce')) {
    /**
     * Null coalescing with ternary awareness.
     * 
     * @example ternary_coalesce($value, default: 'true', ifNull: 'unknown')
     */
    function ternary_coalesce(mixed $value, string|bool $default = false, string|bool $ifNull = null): mixed
    {
        if ($value === null && $ifNull !== null) {
            return is_string($ifNull)
                ? \VinkiusLabs\Trilean\Enums\TernaryState::fromMixed($ifNull)
                : $ifNull;
        }

        try {
            return \VinkiusLabs\Trilean\Enums\TernaryState::fromMixed($value);
        } catch (\InvalidArgumentException) {
            return is_string($default)
                ? \VinkiusLabs\Trilean\Enums\TernaryState::fromMixed($default)
                : $default;
        }
    }
}

// ========================================
// Pipe Helpers
// ========================================

if (!function_exists('pipe_ternary')) {
    /**
     * Transform ternary value through pipeline of functions.
     * 
     * @example 
     * pipe_ternary($value, [
     *     fn($s) => $s->invert(),
     *     fn($s) => validateState($s),
     *     fn($s) => $s->toBool()
     * ])
     */
    function pipe_ternary(mixed $value, array $transformers): mixed
    {
        $state = \VinkiusLabs\Trilean\Enums\TernaryState::fromMixed($value);

        foreach ($transformers as $transformer) {
            $state = $transformer($state);
        }

        return $state;
    }
}

// ========================================
// Fluent Decision Builder
// ========================================

if (!function_exists('decide')) {
    /**
     * Create a fluent decision builder.
     * 
     * @example
     * decide()
     *     ->input('verified', $user->verified)
     *     ->input('consent', $user->consent)
     *     ->and('compliance', ['verified', 'consent'])
     *     ->evaluate();
     * 
     * @example
     * decide($user->verified, $user->consent)
     *     ->requireAll()
     *     ->toBool();
     */
    function decide(mixed ...$inputs): \VinkiusLabs\Trilean\Support\DecisionBuilder
    {
        $engine = app(\VinkiusLabs\Trilean\Decision\TernaryDecisionEngine::class);
        $logic = app(\VinkiusLabs\Trilean\Services\TernaryLogicService::class);

        return new \VinkiusLabs\Trilean\Support\DecisionBuilder($engine, $logic, ...$inputs);
    }
}

// ========================================
// Domain-Specific Helpers
// ========================================

if (!function_exists('gdpr_can_process')) {
    /**
     * GDPR helper for consent and legitimate interest.
     * 
     * @example gdpr_can_process($consent, $legitimate_interest)
     */
    function gdpr_can_process(mixed $consent, mixed $legitimateInterest = null): bool
    {
        return (new \VinkiusLabs\Trilean\Support\Domain\GdprHelper($consent, $legitimateInterest))
            ->canProcess();
    }
}

if (!function_exists('gdpr_requires_action')) {
    /**
     * Check if GDPR consent requires user action.
     */
    function gdpr_requires_action(mixed $consent): bool
    {
        return (new \VinkiusLabs\Trilean\Support\Domain\GdprHelper($consent))
            ->requiresAction();
    }
}

if (!function_exists('feature')) {
    /**
     * Feature flag helper with rollout support.
     * 
     * @example feature($flag)->enabled($user)
     * @example feature($flag)->rollout($user, percentage: 10)
     */
    function feature(mixed $flag): \VinkiusLabs\Trilean\Support\Domain\FeatureHelper
    {
        return new \VinkiusLabs\Trilean\Support\Domain\FeatureHelper($flag);
    }
}

if (!function_exists('risk_level')) {
    /**
     * Risk assessment helper.
     * 
     * @example risk_level($signal1, $signal2, $signal3)->acceptable()
     */
    function risk_level(mixed ...$signals): \VinkiusLabs\Trilean\Support\Domain\RiskHelper
    {
        return new \VinkiusLabs\Trilean\Support\Domain\RiskHelper(...$signals);
    }
}

if (!function_exists('fraud_score')) {
    /**
     * Fraud detection helper.
     * 
     * @example fraud_score($check1, $check2, $check3)->threshold(0.7)
     */
    function fraud_score(mixed ...$checks): \VinkiusLabs\Trilean\Support\Domain\FraudScoreHelper
    {
        return new \VinkiusLabs\Trilean\Support\Domain\FraudScoreHelper(...$checks);
    }
}

if (!function_exists('compliant')) {
    /**
     * Compliance checker helper.
     * 
     * @example compliant($legal, $finance, $security)->strict()
     */
    function compliant(mixed ...$checks): \VinkiusLabs\Trilean\Support\Domain\ComplianceHelper
    {
        return new \VinkiusLabs\Trilean\Support\Domain\ComplianceHelper(...$checks);
    }
}

if (!function_exists('approved')) {
    /**
     * Approval checker helper (alias for compliant).
     * 
     * @example approved($dept1, $dept2, $dept3)->requireAll()
     */
    function approved(mixed ...$approvals): \VinkiusLabs\Trilean\Support\Domain\ComplianceHelper
    {
        return new \VinkiusLabs\Trilean\Support\Domain\ComplianceHelper(...$approvals);
    }
}
