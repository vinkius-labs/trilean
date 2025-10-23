<?php

namespace VinkiusLabs\Trilean\Macros;

use Illuminate\Support\Collection;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Collections\TernaryVector;

class CollectionMacros
{
    public static function register(): void
    {
        /**
         * Convert collection items to ternary states and get consensus.
         * 
         * @example collect($votes)->ternaryConsensus()
         */
        Collection::macro('ternaryConsensus', function () {
            return TernaryVector::make($this->items)->consensus();
        });

        /**
         * Get majority decision from collection of values.
         * 
         * @example collect($healthChecks)->ternaryMajority()
         */
        Collection::macro('ternaryMajority', function () {
            return TernaryVector::make($this->items)->majority();
        });

        /**
         * Filter collection by ternary state.
         * 
         * @example $users->whereTernaryTrue('verified')
         */
        Collection::macro('whereTernaryTrue', function (string $key) {
            return $this->filter(fn($item) => ternary(data_get($item, $key))->isTrue());
        });

        Collection::macro('whereTernaryFalse', function (string $key) {
            return $this->filter(fn($item) => ternary(data_get($item, $key))->isFalse());
        });

        Collection::macro('whereTernaryUnknown', function (string $key) {
            return $this->filter(fn($item) => ternary(data_get($item, $key))->isUnknown());
        });

        /**
         * Weighted decision from collection values.
         * 
         * @example collect($signals)->ternaryWeighted([3, 2, 1])
         */
        Collection::macro('ternaryWeighted', function (array $weights = []) {
            return trilean()->weighted($this->items, $weights);
        });

        /**
         * Map collection and apply ternary logic.
         * 
         * @example $users->ternaryMap(fn($u) => $u->active && $u->verified)
         */
        Collection::macro('ternaryMap', function (callable $callback) {
            return TernaryVector::make($this->map($callback));
        });

        /**
         * Get ternary score of collection.
         * 
         * @example collect($checks)->ternaryScore()
         */
        Collection::macro('ternaryScore', function () {
            return TernaryVector::make($this->items)->score();
        });

        /**
         * Check if all items evaluate to TRUE.
         * 
         * @example collect($conditions)->allTernaryTrue()
         */
        Collection::macro('allTernaryTrue', function () {
            return trilean()->and(...$this->items)->isTrue();
        });

        /**
         * Check if any item evaluates to TRUE.
         * 
         * @example collect($fallbacks)->anyTernaryTrue()
         */
        Collection::macro('anyTernaryTrue', function () {
            return trilean()->or(...$this->items)->isTrue();
        });

        /**
         * Partition collection by ternary states.
         * 
         * @example list($true, $false, $unknown) = $collection->partitionTernary('status')
         */
        Collection::macro('partitionTernary', function (string $key) {
            return [
                $this->whereTernaryTrue($key),
                $this->whereTernaryFalse($key),
                $this->whereTernaryUnknown($key),
            ];
        });

        /**
         * Apply ternary gate to collection and return result.
         * 
         * @example $permissions->ternaryGate('and') // all must be true
         */
        Collection::macro('ternaryGate', function (string $operator = 'and') {
            return match ($operator) {
                'and' => trilean()->and(...$this->items),
                'or' => trilean()->or(...$this->items),
                'xor' => trilean()->xor(...$this->items),
                'consensus' => trilean()->consensus($this->items),
                default => TernaryState::UNKNOWN,
            };
        });
    }
}
