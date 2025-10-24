<?php

namespace VinkiusLabs\Trilean\Support\Domain;

use VinkiusLabs\Trilean\Enums\TernaryState;

/**
 * Feature flag helper with rollout support.
 * 
 * @example feature($flag)->enabled($user)
 * @example feature($flag)->rollout($user, percentage: 10)
 */
class FeatureHelper
{
    private mixed $flag;

    public function __construct(mixed $flag)
    {
        $this->flag = $flag;
    }

    /**
     * Check if feature is enabled for user.
     */
    public function enabled(mixed $user = null): bool
    {
        $state = TernaryState::fromMixed($this->flag);

        if ($state->isTrue()) {
            return true;
        }

        if ($state->isFalse()) {
            return false;
        }

        // Unknown - feature is in rollout/testing
        return false;
    }

    /**
     * Check with percentage-based rollout for unknown state.
     */
    public function rollout(mixed $user, int $percentage = 0): bool
    {
        $state = TernaryState::fromMixed($this->flag);

        // Explicitly enabled
        if ($state->isTrue()) {
            return true;
        }

        // Explicitly disabled
        if ($state->isFalse()) {
            return false;
        }

        // Gradual rollout for unknown state
        if ($user === null) {
            return false;
        }

        // Use user ID for deterministic rollout
        $userId = is_object($user) && isset($user->id) ? $user->id : (int) $user;

        return ($userId % 100) < $percentage;
    }

    /**
     * Get feature status.
     */
    public function status(): string
    {
        return match (TernaryState::fromMixed($this->flag)) {
            TernaryState::TRUE => 'enabled',
            TernaryState::FALSE => 'disabled',
            TernaryState::UNKNOWN => 'testing',
        };
    }

    /**
     * Check if feature is in testing/rollout.
     */
    public function isTesting(): bool
    {
        return is_unknown($this->flag);
    }
}
