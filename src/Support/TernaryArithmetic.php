<?php

namespace VinkiusLabs\Trilean\Support;

use VinkiusLabs\Trilean\Enums\BalancedTrit;
use VinkiusLabs\Trilean\Enums\TernaryState;

class TernaryArithmetic
{
    public function __construct(private BalancedTernaryConverter $converter) {}

    public function add(int $a, int $b): int
    {
        $aTrits = $this->toTrits($a);
        $bTrits = $this->toTrits($b);

        $maxLen = max(count($aTrits), count($bTrits));
        $aTrits = array_pad($aTrits, $maxLen, BalancedTrit::ZERO);
        $bTrits = array_pad($bTrits, $maxLen, BalancedTrit::ZERO);

        $result = [];
        $carry = BalancedTrit::ZERO;

        for ($i = 0; $i < $maxLen; $i++) {
            $sum = $this->addTrits($aTrits[$i], $bTrits[$i], $carry);
            $result[] = $sum['trit'];
            $carry = $sum['carry'];
        }

        if ($carry->toInt() !== 0) {
            $result[] = $carry;
        }

        return $this->fromTrits($result);
    }

    public function subtract(int $a, int $b): int
    {
        return $this->add($a, -$b);
    }

    private function addTrits(BalancedTrit $a, BalancedTrit $b, BalancedTrit $carry): array
    {
        $sum = $a->toInt() + $b->toInt() + $carry->toInt();

        $trit = match (true) {
            $sum > 1 => BalancedTrit::NEGATIVE,
            $sum < -1 => BalancedTrit::POSITIVE,
            default => BalancedTrit::fromInt($sum),
        };

        $carryOut = match (true) {
            $sum > 1 => BalancedTrit::POSITIVE,
            $sum < -1 => BalancedTrit::NEGATIVE,
            default => BalancedTrit::ZERO,
        };

        return ['trit' => $trit, 'carry' => $carryOut];
    }

    private function toTrits(int $decimal): array
    {
        if ($decimal === 0) {
            return [BalancedTrit::ZERO];
        }

        $trits = [];
        $value = $decimal;

        while ($value !== 0) {
            $remainder = $value % 3;
            $value = intdiv($value, 3);

            if ($remainder === 2) {
                $remainder = -1;
                $value++;
            }

            $trits[] = BalancedTrit::fromInt($remainder);
        }

        return $trits;
    }

    private function fromTrits(array $trits): int
    {
        $sum = 0;
        foreach ($trits as $index => $trit) {
            $sum += $trit->toInt() * 3 ** $index;
        }
        return $sum;
    }

    public function normalizeNoise(array $values, float $threshold = 0.5): array
    {
        // Simple noise normalization: if unknown ratio > threshold, treat as noise and consensus
        $total = count($values);

        if ($total === 0) {
            return [];
        }

        $states = array_map(fn($v) => TernaryState::fromMixed($v), $values);

        $unknowns = count(array_filter($states, fn(TernaryState $state) => $state->isUnknown()));

        if ($unknowns / $total > $threshold) {
            $signals = collect($states)
                ->reject(fn(TernaryState $state) => $state->isUnknown())
                ->values();

            if ($signals->isEmpty()) {
                return array_fill(0, $total, TernaryState::UNKNOWN);
            }

            $dominantKey = $signals
                ->countBy(fn(TernaryState $state) => $state->value)
                ->sortDesc()
                ->keys()
                ->first();

            $dominantState = TernaryState::fromMixed($dominantKey);

            return array_fill(0, $total, $dominantState);
        }

        return $states;
    }
}
