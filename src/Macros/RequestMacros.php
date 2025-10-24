<?php

namespace VinkiusLabs\Trilean\Macros;

use Illuminate\Http\Request;

class RequestMacros
{
    public static function register(): void
    {
        /**
         * Direct boolean checks on request values.
         */
        Request::macro('isTrue', function (string $key) {
            return is_true($this->input($key));
        });

        Request::macro('isFalse', function (string $key) {
            return is_false($this->input($key));
        });

        Request::macro('isUnknown', function (string $key) {
            return is_unknown($this->input($key));
        });

        /**
         * Pick value with defaults.
         */
        Request::macro('pick', function (string $key, mixed $ifTrue, mixed $ifFalse, mixed $ifUnknown = null) {
            return pick($this->input($key), $ifTrue, $ifFalse, $ifUnknown);
        });

        /**
         * Require specific ternary states.
         */
        Request::macro('requireTrue', function (string $key, string $message = null) {
            require_true($this->input($key), $message ?? "Field {$key} must be true");
            return $this;
        });

        Request::macro('requireNotFalse', function (string $key, string $message = null) {
            require_not_false($this->input($key), $message ?? "Field {$key} cannot be false");
            return $this;
        });

        /**
         * Check multiple fields at once.
         */
        Request::macro('allTrue', function (array $keys) {
            $values = collect($keys)->map(fn($key) => $this->input($key))->all();
            return and_all(...$values);
        });

        Request::macro('anyTrue', function (array $keys) {
            $values = collect($keys)->map(fn($key) => $this->input($key))->all();
            return or_any(...$values);
        });

        /**
         * Vote on multiple fields.
         */
        Request::macro('vote', function (array $keys) {
            $values = collect($keys)->map(fn($key) => $this->input($key))->all();
            return vote(...$values);
        });
    }
}
