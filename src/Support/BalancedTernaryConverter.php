<?php

namespace VinkiusLabs\Trilean\Support;

use Illuminate\Support\Collection;
use VinkiusLabs\Trilean\Collections\TernaryVector;
use VinkiusLabs\Trilean\Enums\BalancedTrit;
use VinkiusLabs\Trilean\Enums\TernaryState;

class BalancedTernaryConverter
{
    /**
     * Convert an integer to a balanced ternary string representation.
     */
    public function toBalanced(int $decimal): string
    {
        if ($decimal === 0) {
            return '0';
        }

        $digits = [];
        $value = $decimal;

        while ($value !== 0) {
            $remainder = $value % 3;
            $value = intdiv($value, 3);

            if ($remainder === 2) {
                $remainder = -1;
                $value++;
            }

            if ($remainder === -2) {
                $remainder = 1;
                $value--;
            }

            $digits[] = BalancedTrit::fromInt($remainder)->symbol();
        }

        return implode('', array_reverse($digits));
    }

    public function fromBalanced(string $balanced): int
    {
        $normalized = trim($balanced);

        if ($normalized === '') {
            return 0;
        }

        $chars = mb_str_split($normalized);
        $length = count($chars);

        $sum = 0;
        foreach ($chars as $index => $char) {
            $power = $length - $index - 1;
            $trit = BalancedTrit::fromSymbol($char);
            $sum += $trit->toInt() * 3 ** $power;
        }

        return $sum;
    }

    public function encodeStates(iterable $values): string
    {
        $states = collect($values)->map(fn($value) => TernaryState::fromMixed($value));
        return $states->map(fn(TernaryState $state) => $state->toBalancedTrit()->symbol())->implode('');
    }

    public function decodeStates(string $encoded): TernaryVector
    {
        $states = Collection::make(mb_str_split($encoded))
            ->map(fn(string $symbol) => TernaryState::fromBalancedTrit(BalancedTrit::fromSymbol($symbol)));

        return new TernaryVector($states);
    }
}
