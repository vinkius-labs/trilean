<?php

namespace VinkiusLabs\Trilean\Support;

use VinkiusLabs\Trilean\Enums\TernaryState;

/**
 * Advanced pattern matching for ternary values.
 */
class TernaryPatternMatcher
{
    /**
     * Match value against wildcard patterns.
     * 
     * @example 
     * matchWildcard('premium', [
     *     'premium|enterprise' => 'Advanced',
     *     'free|trial' => 'Basic',
     *     '*' => 'Unknown'
     * ])
     */
    public static function matchWildcard(mixed $value, array $patterns): mixed
    {
        $valueStr = (string) $value;

        foreach ($patterns as $pattern => $result) {
            // Default/wildcard match
            if ($pattern === '*' || $pattern === 'default') {
                return $result;
            }

            // Pipe-separated patterns (OR logic)
            if (str_contains($pattern, '|')) {
                $alternatives = explode('|', $pattern);
                foreach ($alternatives as $alt) {
                    if (trim($alt) === $valueStr) {
                        return $result;
                    }
                }
                continue;
            }

            // Exact match
            if ((string) $pattern === $valueStr) {
                return $result;
            }
        }

        // No match found
        throw new \InvalidArgumentException("No pattern matched for value: {$valueStr}");
    }

    /**
     * Match multiple conditions (AND logic within each array).
     * 
     * @example
     * matchConditions(null, [
     *     [true, true] => 'All verified',
     *     [true, false] => 'Partially verified',
     *     'default' => 'Not verified'
     * ])
     */
    public static function matchConditions(mixed $value, array $patterns): mixed
    {
        // If value is an array of conditions, use it; otherwise treat patterns as condition arrays
        $conditions = is_array($value) ? $value : null;

        foreach ($patterns as $pattern => $result) {
            // Default match
            if ($pattern === 'default' || $pattern === '*') {
                return $result;
            }

            // Multi-condition match
            if (is_array($pattern)) {
                $allMatch = true;

                foreach ($pattern as $index => $expected) {
                    // If we have conditions array, check against it
                    if ($conditions !== null && isset($conditions[$index])) {
                        if ($conditions[$index] !== $expected) {
                            $allMatch = false;
                            break;
                        }
                    } else {
                        // Just check if expected is truthy
                        if (!$expected) {
                            $allMatch = false;
                            break;
                        }
                    }
                }

                if ($allMatch) {
                    return $result;
                }
            }
        }

        // Check for default at the end
        if (isset($patterns['default'])) {
            return $patterns['default'];
        }

        if (isset($patterns['*'])) {
            return $patterns['*'];
        }

        throw new \InvalidArgumentException('No pattern matched and no default provided');
    }
}
