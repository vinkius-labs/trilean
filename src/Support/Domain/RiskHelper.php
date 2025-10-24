<?php

namespace VinkiusLabs\Trilean\Support\Domain;

use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Services\TernaryLogicService;

/**
 * Risk assessment helper.
 * 
 * @example risk_level($signal1, $signal2, $signal3)->acceptable()
 * @example fraud_score($check1, $check2)->threshold(0.7)
 */
class RiskHelper
{
    private array $signals;
    private TernaryLogicService $logic;

    public function __construct(mixed ...$signals)
    {
        $this->signals = $signals;
        $this->logic = app(TernaryLogicService::class);
    }

    /**
     * Check if risk is acceptable (majority safe).
     */
    public function acceptable(): bool
    {
        $consensus = $this->logic->consensus($this->signals);

        // Safe if consensus is NOT risky
        return !$consensus->isFalse();
    }

    /**
     * Check if risk exceeds threshold.
     * Threshold is percentage of signals that must be safe (inverted logic).
     */
    public function threshold(float $safeThreshold): bool
    {
        $total = count($this->signals);
        if ($total === 0) {
            return true; // No signals = safe
        }

        $safeCount = 0;
        foreach ($this->signals as $signal) {
            // Invert: true signal = risky, false = safe, unknown = neutral
            $state = TernaryState::fromMixed($signal);
            if ($state->isFalse()) {
                $safeCount++;
            } elseif ($state->isUnknown()) {
                $safeCount += 0.5; // Unknown counts as half-safe
            }
        }

        $safeRatio = $safeCount / $total;

        return $safeRatio >= $safeThreshold;
    }

    /**
     * Get risk level label.
     */
    public function level(): string
    {
        $consensus = $this->logic->consensus($this->signals);

        return match ($consensus) {
            TernaryState::TRUE => 'high',      // Consensus says risky
            TernaryState::FALSE => 'low',      // Consensus says safe
            TernaryState::UNKNOWN => 'medium', // Unclear
        };
    }

    /**
     * Count risky signals.
     */
    public function riskyCount(): int
    {
        $count = 0;
        foreach ($this->signals as $signal) {
            if (is_true($signal)) {
                $count++;
            }
        }
        return $count;
    }
}

/**
 * Fraud score helper (alias for risk with inverted semantics).
 */
class FraudScoreHelper extends RiskHelper
{
    /**
     * Check if fraud score is below threshold (safe).
     */
    public function threshold(float $fraudThreshold): bool
    {
        // Invert threshold logic for fraud
        return parent::threshold(1.0 - $fraudThreshold);
    }
}
