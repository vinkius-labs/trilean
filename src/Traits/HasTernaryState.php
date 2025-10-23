<?php

namespace VinkiusLabs\Trilean\Traits;

use VinkiusLabs\Trilean\Enums\TernaryState;

trait HasTernaryState
{
    protected TernaryState $ternaryState = TernaryState::UNKNOWN;

    public function getTernaryState(): TernaryState
    {
        return $this->ternaryState;
    }

    public function setTernaryState(mixed $state): void
    {
        $this->ternaryState = TernaryState::fromMixed($state);
    }

    public function isTernaryTrue(): bool
    {
        return $this->ternaryState->isTrue();
    }

    public function isTernaryFalse(): bool
    {
        return $this->ternaryState->isFalse();
    }

    public function isTernaryUnknown(): bool
    {
        return $this->ternaryState->isUnknown();
    }

    public function ternaryStateLabel(): string
    {
        return $this->ternaryState->label();
    }
}
