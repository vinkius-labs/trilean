<?php

use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Collections\TernaryVector;

if (!function_exists('ternary')) {
    /**
     * Convert any value to a TernaryState with fluent syntax.
     * 
     * @example ternary($user->verified)->isTrue()
     * @example ternary(null)->isUnknown()
     */
    function ternary(mixed $value): TernaryState
    {
        return TernaryState::fromMixed($value);
    }
}

if (!function_exists('maybe')) {
    /**
     * Smart ternary evaluation: returns value if TRUE, default if FALSE, callback if UNKNOWN.
     * 
     * @example maybe($consent, 'Allowed', 'Denied', fn() => 'Pending approval')
     */
    function maybe(mixed $condition, mixed $ifTrue, mixed $ifFalse, mixed $ifUnknown = null): mixed
    {
        $state = TernaryState::fromMixed($condition);

        return match ($state) {
            TernaryState::TRUE => value($ifTrue),
            TernaryState::FALSE => value($ifFalse),
            TernaryState::UNKNOWN => value($ifUnknown ?? $ifFalse),
        };
    }
}

if (!function_exists('trilean')) {
    /**
     * Quick access to the ternary logic service.
     * 
     * @example trilean()->and($a, $b, $c)
     * @example trilean()->weighted([$x, $y], [3, 1])
     */
    function trilean(): \VinkiusLabs\Trilean\Services\TernaryLogicService
    {
        return app('trilean.logic');
    }
}

if (!function_exists('ternary_vector')) {
    /**
     * Create a TernaryVector from an array or collection.
     * 
     * @example ternary_vector([true, false, null])->consensus()
     */
    function ternary_vector(iterable $values): TernaryVector
    {
        return TernaryVector::make($values);
    }
}

if (!function_exists('all_true')) {
    /**
     * Check if all values are TRUE (strict AND).
     * 
     * @example all_true($verified, $consented, $active)
     */
    function all_true(mixed ...$values): bool
    {
        return trilean()->and(...$values)->isTrue();
    }
}

if (!function_exists('any_true')) {
    /**
     * Check if any value is TRUE (relaxed OR).
     * 
     * @example any_true($method1, $method2, $method3)
     */
    function any_true(mixed ...$values): bool
    {
        return trilean()->or(...$values)->isTrue();
    }
}

if (!function_exists('none_false')) {
    /**
     * Check if none of the values are FALSE (allows UNKNOWN).
     * 
     * @example none_false($check1, $check2) // returns true if all are TRUE or UNKNOWN
     */
    function none_false(mixed ...$values): bool
    {
        return !trilean()->or(...$values)->isFalse() || trilean()->and(...$values)->isUnknown();
    }
}

if (!function_exists('when_ternary')) {
    /**
     * Conditional execution based on ternary state.
     * 
     * @example when_ternary($state, fn() => $user->activate(), fn() => $user->block())
     */
    function when_ternary(
        mixed $condition,
        ?callable $onTrue = null,
        ?callable $onFalse = null,
        ?callable $onUnknown = null
    ): mixed {
        $state = TernaryState::fromMixed($condition);

        return match ($state) {
            TernaryState::TRUE => $onTrue ? $onTrue() : true,
            TernaryState::FALSE => $onFalse ? $onFalse() : false,
            TernaryState::UNKNOWN => $onUnknown ? $onUnknown() : null,
        };
    }
}

if (!function_exists('consensus')) {
    /**
     * Find consensus among multiple signals.
     * 
     * @example consensus($vote1, $vote2, $vote3)->label()
     */
    function consensus(mixed ...$values): TernaryState
    {
        return trilean()->consensus($values);
    }
}

if (!function_exists('ternary_match')) {
    /**
     * Pattern matching for ternary states (syntactic sugar).
     * 
     * @example ternary_match($state, [
     *     'true' => 'Approved',
     *     'false' => 'Rejected', 
     *     'unknown' => 'Pending'
     * ])
     */
    function ternary_match(mixed $value, array $cases): mixed
    {
        $state = TernaryState::fromMixed($value);
        $key = strtolower($state->value);

        $match = $cases[$key] ?? ($cases['unknown'] ?? null);

        if ($match !== null) {
            return value($match, $state->value, $state);
        }

        if (array_key_exists('any', $cases)) {
            return value($cases['any'], $state->value, $state);
        }

        return null;
    }
}

if (!function_exists('ternary_badge')) {
    /**
     * Render a ternary badge using the package view helper.
     */
    function ternary_badge(mixed $state, array $options = []): string
    {
        $payload = array_merge(['state' => $state], $options);

        return view('trilean::components.badge', $payload)->render();
    }
}

if (!function_exists('ternary_icon')) {
    /**
     * Resolve icon markup for a ternary state.
     */
    function ternary_icon(mixed $value, ?string $trueIcon = null, ?string $falseIcon = null, ?string $unknownIcon = null): string
    {
        $state = TernaryState::fromMixed($value);

        $defaults = config('trilean.ui.icons', []);
        $overrides = array_filter([
            'true' => $trueIcon,
            'false' => $falseIcon,
            'unknown' => $unknownIcon,
        ], fn($icon) => $icon !== null && $icon !== '');

        $icons = array_merge($defaults, $overrides);
        $class = $icons[$state->value] ?? 'heroicon-o-question-mark-circle';

        return '<i class="' . e($class) . '"></i>';
    }
}
