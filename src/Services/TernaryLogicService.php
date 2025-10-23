<?php

namespace VinkiusLabs\Trilean\Services;

use Illuminate\Support\Collection;
use VinkiusLabs\Trilean\Collections\TernaryVector;
use VinkiusLabs\Trilean\Enums\BalancedTrit;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Support\BalancedTernaryConverter;

class TernaryLogicService
{
    public function __construct(
        private readonly BalancedTernaryConverter $converter,
        private ?TernaryExpressionEvaluator $expressionEvaluator = null,
    ) {}

    public function setExpressionEvaluator(TernaryExpressionEvaluator $evaluator): void
    {
        $this->expressionEvaluator = $evaluator;
    }

    public function normalise(mixed $value): TernaryState
    {
        return TernaryState::fromMixed($value);
    }

    public function and(mixed ...$values): TernaryState
    {
        $vector = $this->vector($values);

        if ($vector->contains(fn(TernaryState $state) => $state->isFalse())) {
            return TernaryState::FALSE;
        }

        if ($vector->contains(fn(TernaryState $state) => $state->isUnknown())) {
            return TernaryState::UNKNOWN;
        }

        return TernaryState::TRUE;
    }

    public function or(mixed ...$values): TernaryState
    {
        $vector = $this->vector($values);

        if ($vector->contains(fn(TernaryState $state) => $state->isTrue())) {
            return TernaryState::TRUE;
        }

        if ($vector->contains(fn(TernaryState $state) => $state->isUnknown())) {
            return TernaryState::UNKNOWN;
        }

        return TernaryState::FALSE;
    }

    public function xor(mixed ...$values): TernaryState
    {
        $vector = $this->vector($values);
        $positives = $vector->filter(fn(TernaryState $state) => $state->isTrue())->count();
        $negatives = $vector->filter(fn(TernaryState $state) => $state->isFalse())->count();

        if ($positives === $negatives) {
            return TernaryState::UNKNOWN;
        }

        return $positives > $negatives ? TernaryState::TRUE : TernaryState::FALSE;
    }

    public function not(mixed $value): TernaryState
    {
        return TernaryState::fromMixed($value)->invert();
    }

    public function consensus(iterable $values): TernaryState
    {
        return $this->weighted($values, array_fill(0, count($this->vector($values)->all()), 1));
    }

    public function weighted(iterable $values, iterable $weights): TernaryState
    {
        $vector = $this->vector($values);
        $weightsCollection = Collection::make($weights)->values();

        if ($weightsCollection->isEmpty()) {
            $weightsCollection = Collection::make(array_fill(0, $vector->count(), 1));
        }

        $weightsArray = $weightsCollection
            ->map(fn($weight) => (int) $weight)
            ->values();

        $score = $vector->reduce(function ($carry, TernaryState $state, $index) use ($weightsArray) {
            $weight = $weightsArray->get($index, 1);

            return $carry + $state->toBalancedTrit()->toInt() * $weight;
        }, 0);

        return match (BalancedTrit::fromInt($score)) {
            BalancedTrit::POSITIVE => TernaryState::TRUE,
            BalancedTrit::NEGATIVE => TernaryState::FALSE,
            BalancedTrit::ZERO => TernaryState::UNKNOWN,
        };
    }

    public function vector(iterable $values): TernaryVector
    {
        return TernaryVector::make($values);
    }

    public function expression(string $expression, array $context = []): TernaryState
    {
        if ($this->expressionEvaluator === null) {
            throw new \RuntimeException('Expression evaluator not configured.');
        }

        return $this->expressionEvaluator->evaluate($expression, $context, $this);
    }

    public function encode(iterable $values): string
    {
        return $this->converter->encodeStates($values);
    }

    public function decode(string $tercode): TernaryVector
    {
        return $this->converter->decodeStates($tercode);
    }
}
