<?php

namespace VinkiusLabs\Trilean\Validation;

use Illuminate\Support\Facades\Validator;

class ValidationRules
{
    public static function register(): void
    {
        /**
         * Simple validation rules with clear names.
         */

        Validator::extend('must_be_true', function ($attribute, $value) {
            return is_true($value);
        }, 'The :attribute must be true.');

        Validator::extend('cannot_be_false', function ($attribute, $value) {
            return !is_false($value);
        }, 'The :attribute cannot be false.');

        Validator::extend('must_be_known', function ($attribute, $value) {
            return !is_unknown($value);
        }, 'The :attribute must have a definite value (not unknown).');

        Validator::extend('all_must_be_true', function ($attribute, $value) {
            if (!is_array($value)) return false;
            return and_all(...$value);
        }, 'All values in :attribute must be true.');

        Validator::extend('any_must_be_true', function ($attribute, $value) {
            if (!is_array($value)) return false;
            return or_any(...$value);
        }, 'At least one value in :attribute must be true.');

        Validator::extend('majority_true', function ($attribute, $value) {
            if (!is_array($value)) return false;
            return vote(...$value) === 'true';
        }, 'The majority of values in :attribute must be true.');

        /**
         * Conditional validation based on other fields.
         */
        Validator::extend('true_if', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 2) return true;

            $otherField = $parameters[0];
            $otherValue = $parameters[1];
            $otherFieldValue = data_get($validator->getData(), $otherField);

            // If other field matches the condition, this field must be true
            if ($otherFieldValue == $otherValue) {
                return is_true($value);
            }

            return true;
        }, 'The :attribute must be true when :other is :value.');

        Validator::extend('false_if', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 2) return true;

            $otherField = $parameters[0];
            $otherValue = $parameters[1];
            $otherFieldValue = data_get($validator->getData(), $otherField);

            // If other field matches the condition, this field must be false
            if ($otherFieldValue == $otherValue) {
                return is_false($value);
            }

            return true;
        }, 'The :attribute must be false when :other is :value.');
    }
}
