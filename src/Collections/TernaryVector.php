<?php

namespace VinkiusLabs\Trilean\Collections;

use Illuminate\Support\Collection;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Support\BalancedTernaryConverter;
use VinkiusLabs\Trilean\Enums\BalancedTrit;

class TernaryVector extends Collection
{
    public function __construct(mixed $items = [])
    {
        parent::__construct($this->normalise($items));
    }

    public static function make($items = null): self
    {
        return new self($items ?? []);
    }

    public function and(): TernaryState
    {
        $logic = app('trilean.logic');

        return $logic->and($this->all());
    }

    public function or(): TernaryState
    {
        $logic = app('trilean.logic');

        return $logic->or($this->all());
    }

    public function consensus(): TernaryState
    {
        $logic = app('trilean.logic');

        return $logic->consensus($this->items);
    }

    public function majority(): TernaryState
    {
        $logic = app('trilean.logic');

        return $logic->weighted($this->items, array_fill(0, $this->count(), 1));
    }

    public function toBalancedString(): string
    {
        return app(BalancedTernaryConverter::class)->encodeStates($this->items);
    }

    public function score(): int
    {
        return $this->sum(fn(TernaryState $state) => $state->toInt());
    }

    /** @return array<int, TernaryState> */
    private function normalise(mixed $items): array
    {
        return collect($items)
            ->flatten()
            ->map(fn(mixed $value) => TernaryState::fromMixed($value))
            ->values()
            ->all();
    }
}
