<?php

namespace VinkiusLabs\Trilean\Support;

use VinkiusLabs\Trilean\Enums\TernaryState;

/**
 * Fluent builder for chaining ternary operations.
 * 
 * @example 
 * ternary($value)
 *     ->ifTrue('Premium')
 *     ->ifFalse('Free')
 *     ->ifUnknown('Trial')
 *     ->resolve();
 * 
 * @example
 * ternary($flag)
 *     ->whenTrue(fn() => activatePremium())
 *     ->whenFalse(fn() => logDisabled())
 *     ->whenUnknown(fn() => queueReview())
 *     ->execute();
 */
class TernaryFluentBuilder
{
    private mixed $ifTrue = null;
    private mixed $ifFalse = null;
    private mixed $ifUnknown = null;

    private mixed $whenTrue = null;
    private mixed $whenFalse = null;
    private mixed $whenUnknown = null;

    public function __construct(
        private readonly TernaryState $state,
        mixed $ifTrue = null,
        mixed $ifFalse = null,
        mixed $ifUnknown = null,
        mixed $whenTrue = null,
        mixed $whenFalse = null,
        mixed $whenUnknown = null,
    ) {
        $this->ifTrue = $ifTrue;
        $this->ifFalse = $ifFalse;
        $this->ifUnknown = $ifUnknown;
        $this->whenTrue = $whenTrue;
        $this->whenFalse = $whenFalse;
        $this->whenUnknown = $whenUnknown;
    }

    /**
     * Set value to return when state is TRUE.
     */
    public function ifTrue(mixed $value): self
    {
        $this->ifTrue = $value;
        return $this;
    }

    /**
     * Set value to return when state is FALSE.
     */
    public function ifFalse(mixed $value): self
    {
        $this->ifFalse = $value;
        return $this;
    }

    /**
     * Set value to return when state is UNKNOWN.
     */
    public function ifUnknown(mixed $value): self
    {
        $this->ifUnknown = $value;
        return $this;
    }

    /**
     * Execute callback when state is TRUE.
     */
    public function whenTrue(callable $callback): self
    {
        $this->whenTrue = $callback;
        return $this;
    }

    /**
     * Execute callback when state is FALSE.
     */
    public function whenFalse(callable $callback): self
    {
        $this->whenFalse = $callback;
        return $this;
    }

    /**
     * Execute callback when state is UNKNOWN.
     */
    public function whenUnknown(callable $callback): self
    {
        $this->whenUnknown = $callback;
        return $this;
    }

    /**
     * Resolve to value based on if* methods.
     */
    public function resolve(): mixed
    {
        return match ($this->state) {
            TernaryState::TRUE => $this->ifTrue ?? throw new \LogicException('No ifTrue value set'),
            TernaryState::FALSE => $this->ifFalse ?? throw new \LogicException('No ifFalse value set'),
            TernaryState::UNKNOWN => $this->ifUnknown ?? $this->ifFalse ?? throw new \LogicException('No ifUnknown value set'),
        };
    }

    /**
     * Execute callbacks based on when* methods.
     */
    public function execute(): mixed
    {
        return match ($this->state) {
            TernaryState::TRUE => $this->whenTrue ? ($this->whenTrue)() : null,
            TernaryState::FALSE => $this->whenFalse ? ($this->whenFalse)() : null,
            TernaryState::UNKNOWN => $this->whenUnknown ? ($this->whenUnknown)() : null,
        };
    }

    /**
     * Get the underlying TernaryState.
     */
    public function state(): TernaryState
    {
        return $this->state;
    }

    /**
     * Convert to boolean.
     */
    public function toBool(bool $unknownAs = false): bool
    {
        return $this->state->toBool($unknownAs);
    }

    /**
     * Transform state through a pipeline.
     */
    public function pipe(callable $transformer): TernaryState
    {
        return $transformer($this->state);
    }
}
