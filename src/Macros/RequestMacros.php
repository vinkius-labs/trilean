<?php

namespace VinkiusLabs\Trilean\Macros;

use Illuminate\Http\Request;
use VinkiusLabs\Trilean\Enums\TernaryState;

class RequestMacros
{
    public static function register(): void
    {
        /**
         * Get ternary state from request input.
         * 
         * @example $request->ternary('consent')
         */
        Request::macro('ternary', function (string $key, mixed $default = null) {
            $value = $this->input($key, $default);
            return TernaryState::fromMixed($value);
        });

        /**
         * Check if request has a TRUE ternary value.
         * 
         * @example $request->hasTernaryTrue('verified')
         */
        Request::macro('hasTernaryTrue', function (string $key) {
            return $this->ternary($key)->isTrue();
        });

        Request::macro('hasTernaryFalse', function (string $key) {
            return $this->ternary($key)->isFalse();
        });

        Request::macro('hasTernaryUnknown', function (string $key) {
            return $this->ternary($key)->isUnknown();
        });

        /**
         * Validate multiple ternary conditions from request.
         * 
         * @example $request->ternaryGate(['consent', 'verified', 'active'], 'and')
         */
        Request::macro('ternaryGate', function (array $keys, string $operator = 'and') {
            $values = collect($keys)->map(fn($key) => $this->ternary($key));

            return match ($operator) {
                'and' => trilean()->and(...$values),
                'or' => trilean()->or(...$values),
                'consensus' => trilean()->consensus($values),
                default => TernaryState::UNKNOWN,
            };
        });

        /**
         * Evaluate ternary expression from request data.
         * 
         * @example $request->ternaryExpression('consent AND !blocked')
         */
        Request::macro('ternaryExpression', function (string $expression) {
            return trilean()->expression($expression, $this->all());
        });
    }
}
