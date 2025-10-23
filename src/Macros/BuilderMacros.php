<?php

namespace VinkiusLabs\Trilean\Macros;

use Illuminate\Database\Eloquent\Builder;
use VinkiusLabs\Trilean\Enums\TernaryState;

class BuilderMacros
{
    public static function register(): void
    {
        /**
         * Filter models by TRUE ternary state.
         * 
         * @example User::whereTernaryTrue('verified')->get()
         */
        Builder::macro('whereTernaryTrue', function (string $column) {
            return $this->where(function ($query) use ($column) {
                $query->where($column, true)
                    ->orWhere($column, 1)
                    ->orWhere($column, 'true')
                    ->orWhere($column, 'yes');
            });
        });

        /**
         * Filter models by FALSE ternary state.
         * 
         * @example User::whereTernaryFalse('blocked')->get()
         */
        Builder::macro('whereTernaryFalse', function (string $column) {
            return $this->where(function ($query) use ($column) {
                $query->where($column, false)
                    ->orWhere($column, 0)
                    ->orWhere($column, 'false')
                    ->orWhere($column, 'no');
            });
        });

        /**
         * Filter models by UNKNOWN ternary state.
         * 
         * @example User::whereTernaryUnknown('consent')->get()
         */
        Builder::macro('whereTernaryUnknown', function (string $column) {
            return $this->whereNull($column);
        });

        /**
         * Order by ternary state (TRUE first, then UNKNOWN, then FALSE).
         * 
         * @example User::orderByTernary('verified')->get()
         */
        Builder::macro('orderByTernary', function (string $column, string $direction = 'desc') {
            return $this->orderByRaw("
                CASE 
                    WHEN {$column} IN (1, true, 'true', 'yes') THEN 1
                    WHEN {$column} IS NULL THEN 0
                    ELSE -1
                END {$direction}
            ");
        });

        /**
         * Get models where ALL specified columns are TRUE.
         * 
         * @example User::whereAllTernaryTrue(['verified', 'active', 'consented'])
         */
        Builder::macro('whereAllTernaryTrue', function (array $columns) {
            $query = $this;
            foreach ($columns as $column) {
                $query = $query->whereTernaryTrue($column);
            }
            return $query;
        });

        /**
         * Get models where ANY specified column is TRUE.
         * 
         * @example User::whereAnyTernaryTrue(['email_verified', 'phone_verified'])
         */
        Builder::macro('whereAnyTernaryTrue', function (array $columns) {
            return $this->where(function ($query) use ($columns) {
                foreach ($columns as $column) {
                    $query->orWhere(function ($q) use ($column) {
                        $q->whereTernaryTrue($column);
                    });
                }
            });
        });

        /**
         * Scope for ternary consensus across multiple columns.
         * 
         * @example User::ternaryConsensus(['check1', 'check2', 'check3'], 'true')
         */
        Builder::macro('ternaryConsensus', function (array $columns, string $expectedState = 'true') {
            return $this->get()->filter(function ($model) use ($columns, $expectedState) {
                $values = collect($columns)->map(fn($col) => $model->$col);
                $state = trilean()->consensus($values);
                return $state->value === $expectedState;
            });
        });
    }
}
