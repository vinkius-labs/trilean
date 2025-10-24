<?php

namespace VinkiusLabs\Trilean\Macros;

use Illuminate\Support\Collection;

class CollectionMacros
{
    public static function register(): void
    {
        /**
         * Simple boolean aggregations.
         */
        Collection::macro('allTrue', function () {
            return and_all(...$this->all());
        });

        Collection::macro('anyTrue', function () {
            return or_any(...$this->all());
        });

        /**
         * Filter by ternary states.
         */
        Collection::macro('onlyTrue', function () {
            return $this->filter(fn($item) => is_true($item));
        });

        Collection::macro('onlyFalse', function () {
            return $this->filter(fn($item) => is_false($item));
        });

        Collection::macro('onlyUnknown', function () {
            return $this->filter(fn($item) => is_unknown($item));
        });

        /**
         * Convert all to safe booleans.
         */
        Collection::macro('toBooleans', function (bool $defaultForUnknown = false) {
            return $this->map(fn($item) => safe_bool($item, $defaultForUnknown));
        });

        /**
         * Simple vote.
         */
        Collection::macro('vote', function () {
            return vote(...$this->all());
        });

        /**
         * Count by ternary states.
         */
        Collection::macro('countTrue', function () {
            return $this->filter(fn($item) => is_true($item))->count();
        });

        Collection::macro('countFalse', function () {
            return $this->filter(fn($item) => is_false($item))->count();
        });

        Collection::macro('countUnknown', function () {
            return $this->filter(fn($item) => is_unknown($item))->count();
        });
    }
}
