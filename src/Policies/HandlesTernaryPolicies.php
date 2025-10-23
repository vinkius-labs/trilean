<?php

namespace VinkiusLabs\Trilean\Policies;

use Illuminate\Auth\Access\AuthorizationException;
use VinkiusLabs\Trilean\Enums\TernaryState;
use function ternary;

trait HandlesTernaryPolicies
{
    protected function allowWhenTrue(mixed $decision, ?string $message = null): bool
    {
        $state = ternary($decision);

        if ($state->isTrue()) {
            return true;
        }

        if ($state->isUnknown()) {
            $config = config('trilean.policies');

            if (($config['throw_on_unknown'] ?? false) === true) {
                throw new AuthorizationException($message ?? $config['unknown_message']);
            }

            return $config['unknown_resolves_to'] ?? false;
        }

        return false;
    }
}
