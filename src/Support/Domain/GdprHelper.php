<?php

namespace VinkiusLabs\Trilean\Support\Domain;

use VinkiusLabs\Trilean\Enums\TernaryState;

/**
 * GDPR-specific helper builder.
 * 
 * @example gdpr_can_process($consent, $legitimate_interest)
 */
class GdprHelper
{
    public function __construct(
        private readonly mixed $consent,
        private readonly mixed $legitimateInterest = null
    ) {}

    /**
     * Check if data processing is allowed.
     */
    public function canProcess(): bool
    {
        $consentState = TernaryState::fromMixed($this->consent);

        // Explicit consent grants permission
        if ($consentState->isTrue()) {
            return true;
        }

        // Explicit rejection denies permission
        if ($consentState->isFalse()) {
            return false;
        }

        // Unknown consent - check legitimate interest
        if ($this->legitimateInterest !== null) {
            return is_true($this->legitimateInterest);
        }

        return false;
    }

    /**
     * Check if consent action is required.
     */
    public function requiresAction(): bool
    {
        return is_unknown($this->consent);
    }

    /**
     * Get consent status label.
     */
    public function status(): string
    {
        return match (TernaryState::fromMixed($this->consent)) {
            TernaryState::TRUE => 'granted',
            TernaryState::FALSE => 'denied',
            TernaryState::UNKNOWN => 'pending',
        };
    }
}
