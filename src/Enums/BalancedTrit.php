<?php

namespace VinkiusLabs\Trilean\Enums;

enum BalancedTrit: int
{
    case POSITIVE = 1;
    case ZERO = 0;
    case NEGATIVE = -1;

    public static function fromInt(int $value): self
    {
        return match (true) {
            $value > 0 => self::POSITIVE,
            $value < 0 => self::NEGATIVE,
            default => self::ZERO,
        };
    }

    public static function fromSymbol(string $symbol): self
    {
        $trimmed = trim($symbol);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('Balanced trit symbol cannot be empty.');
        }

        $upper = strtoupper($trimmed);

        return match ($upper) {
            '+', '1', 'T', 'TRUE', 'P', 'POS', 'POSITIVE' => self::POSITIVE,
            '0', '.', 'Z', 'U', 'UNK', 'UNKNOWN' => self::ZERO,
            '-', "\u{2212}", '−', 'F', 'FALSE', 'N', 'NEG', 'NEGATIVE' => self::NEGATIVE,
            default => throw new \InvalidArgumentException("Unrecognised balanced trit symbol: {$symbol}"),
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::POSITIVE => '+',
            self::ZERO => '0',
            self::NEGATIVE => '-',
        };
    }

    public function invert(): self
    {
        return match ($this) {
            self::POSITIVE => self::NEGATIVE,
            self::NEGATIVE => self::POSITIVE,
            self::ZERO => self::ZERO,
        };
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
