<?php

namespace VinkiusLabs\Trilean\Validation;

use Illuminate\Support\Facades\Validator;
use VinkiusLabs\Trilean\Enums\TernaryState;

class TernaryValidationRules
{
    public static function register(): void
    {
        /**
         * Validate that a field is a valid ternary state.
         * 
         * Usage: 'consent' => 'ternary'
         */
        Validator::extend('ternary', function ($attribute, $value, $parameters, $validator) {
            try {
                TernaryState::fromMixed($value);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }, 'The :attribute must be a valid ternary value (true, false, null, or their equivalents).');

        /**
         * Validate that a field is TRUE in ternary logic.
         * 
         * Usage: 'terms_accepted' => 'ternary_true'
         */
        Validator::extend('ternary_true', function ($attribute, $value, $parameters, $validator) {
            return ternary($value)->isTrue();
        }, 'The :attribute must be true.');

        /**
         * Validate that a field is NOT FALSE (allows TRUE or UNKNOWN).
         * 
         * Usage: 'consent' => 'ternary_not_false'
         */
        Validator::extend('ternary_not_false', function ($attribute, $value, $parameters, $validator) {
            return !ternary($value)->isFalse();
        }, 'The :attribute cannot be false.');

        /**
         * Validate ternary gate across multiple fields.
         * 
         * Usage: 'eligibility' => 'ternary_gate:consent,verified,active,and'
         */
        Validator::extend('ternary_gate', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            $operator = array_pop($parameters) ?? 'and';

            $values = collect($parameters)->map(fn($key) => $data[$key] ?? null);

            $state = match ($operator) {
                'and' => trilean()->and(...$values),
                'or' => trilean()->or(...$values),
                'consensus' => trilean()->consensus($values),
                default => TernaryState::UNKNOWN,
            };

            return $state->isTrue();
        }, 'The :attribute requires all related fields to pass ternary validation.');

        /**
         * Validate that at least one of the specified fields is TRUE.
         * 
         * Usage: 'verification' => 'ternary_any_true:email_verified,phone_verified'
         */
        Validator::extend('ternary_any_true', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            $values = collect($parameters)->map(fn($key) => $data[$key] ?? null);

            return trilean()->or(...$values)->isTrue();
        }, 'At least one of the related fields must be true.');

        /**
         * Validate that all specified fields are TRUE.
         * 
         * Usage: 'access' => 'ternary_all_true:verified,active,consented'
         */
        Validator::extend('ternary_all_true', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            $values = collect($parameters)->map(fn($key) => $data[$key] ?? null);

            return trilean()->and(...$values)->isTrue();
        }, 'All related fields must be true.');

        /**
         * Validate consensus among fields.
         * 
         * Usage: 'approval' => 'ternary_consensus:reviewer_approved,editor_approved,admin_approved'
         */
        Validator::extend('ternary_consensus', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            $values = collect($parameters)->map(fn($key) => $data[$key] ?? null);

            $consensus = trilean()->consensus($values);
            return $consensus->isTrue();
        }, 'There must be consensus among the related fields.');

        /**
         * Validate weighted ternary decision.
         * 
         * Usage: 'decision' => 'ternary_weighted:field1:3,field2:2,field3:1'
         */
        Validator::extend('ternary_weighted', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            $values = [];
            $weights = [];

            foreach ($parameters as $param) {
                list($field, $weight) = explode(':', $param . ':1');
                $values[] = $data[trim($field)] ?? null;
                $weights[] = (int) trim($weight);
            }

            $state = trilean()->weighted($values, $weights);
            return $state->isTrue();
        }, 'The weighted ternary decision did not pass validation.');

        /**
         * Validate ternary expression.
         * 
         * Usage: 'eligibility' => 'ternary_expression:consent AND verified AND !blocked'
         */
        Validator::extend('ternary_expression', function ($attribute, $value, $parameters, $validator) {
            $expression = implode(' ', $parameters);
            $data = $validator->getData();

            try {
                $state = trilean()->expression($expression, $data);
                return $state->isTrue();
            } catch (\Exception $e) {
                return false;
            }
        }, 'The :attribute failed ternary expression validation.');
    }
}
