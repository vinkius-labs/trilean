<?php

namespace VinkiusLabs\Trilean\Enums;

use Illuminate\Support\Str;
use InvalidArgumentException;
use VinkiusLabs\Trilean\Support\TernaryFluentBuilder;

enum TernaryState: string
{
    case TRUE = 'true';
    case FALSE = 'false';
    case UNKNOWN = 'unknown';

    private const BOOL_MAP = [
        true => self::TRUE,
        false => self::FALSE,
    ];

    private const STRING_ALIASES = [
        'true' => self::TRUE,
        '1' => self::TRUE,
        'yes' => self::TRUE,
        'on' => self::TRUE,
        'enable' => self::TRUE,
        'enabled' => self::TRUE,
        'y' => self::TRUE,
        'affirmative' => self::TRUE,
        'false' => self::FALSE,
        '0' => self::FALSE,
        'no' => self::FALSE,
        'off' => self::FALSE,
        'disable' => self::FALSE,
        'disabled' => self::FALSE,
        'n' => self::FALSE,
        'negative' => self::FALSE,
        'unknown' => self::UNKNOWN,
        'null' => self::UNKNOWN,
        'undefined' => self::UNKNOWN,
        'pending' => self::UNKNOWN,
        'maybe' => self::UNKNOWN,
        'auto' => self::UNKNOWN,
    ];

    private const INTEGER_ALIASES = [
        1 => self::TRUE,
        0 => self::FALSE,
        -1 => self::UNKNOWN,
    ];

    public static function fromMixed(mixed $value): self
    {
        return match (true) {
            $value instanceof self => $value,
            is_bool($value) => self::BOOL_MAP[$value],
            $value === null => self::UNKNOWN,
            is_string($value) => self::fromString($value),
            is_int($value) => self::fromInt($value),
            default => throw new InvalidArgumentException('Unsupported value type for ternary conversion: ' . get_debug_type($value)),
        };
    }

    public static function fromBalancedTrit(BalancedTrit $trit): self
    {
        return match ($trit) {
            BalancedTrit::POSITIVE => self::TRUE,
            BalancedTrit::NEGATIVE => self::FALSE,
            BalancedTrit::ZERO => self::UNKNOWN,
        };
    }

    public function toBalancedTrit(): BalancedTrit
    {
        return match ($this) {
            self::TRUE => BalancedTrit::POSITIVE,
            self::FALSE => BalancedTrit::NEGATIVE,
            self::UNKNOWN => BalancedTrit::ZERO,
        };
    }

    public function invert(): self
    {
        return match ($this) {
            self::TRUE => self::FALSE,
            self::FALSE => self::TRUE,
            self::UNKNOWN => self::UNKNOWN,
        };
    }

    public function isTrue(): bool
    {
        return $this === self::TRUE;
    }

    public function isFalse(): bool
    {
        return $this === self::FALSE;
    }

    public function isUnknown(): bool
    {
        return $this === self::UNKNOWN;
    }

    public function toInt(): int
    {
        return match ($this) {
            self::TRUE => 1,
            self::FALSE => -1,
            self::UNKNOWN => 0,
        };
    }

    public function toNullableBool(): ?bool
    {
        return match ($this) {
            self::TRUE => true,
            self::FALSE => false,
            self::UNKNOWN => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::TRUE => 'True',
            self::FALSE => 'False',
            self::UNKNOWN => 'Unknown',
        };
    }

    // ========================================
    // Fluent API Methods
    // ========================================

    /**
     * Set value to return when state is TRUE.
     * 
     * @example ternary($value)->ifTrue('Premium')->ifFalse('Free')->resolve()
     */
    public function ifTrue(mixed $value): TernaryFluentBuilder
    {
        return new TernaryFluentBuilder($this, ifTrue: $value);
    }

    /**
     * Set value to return when state is FALSE.
     */
    public function ifFalse(mixed $value): TernaryFluentBuilder
    {
        return new TernaryFluentBuilder($this, ifFalse: $value);
    }

    /**
     * Set value to return when state is UNKNOWN.
     */
    public function ifUnknown(mixed $value): TernaryFluentBuilder
    {
        return new TernaryFluentBuilder($this, ifUnknown: $value);
    }

    /**
     * Execute callback when state is TRUE.
     * 
     * @example ternary($flag)->whenTrue(fn() => activatePremium())->execute()
     */
    public function whenTrue(callable $callback): TernaryFluentBuilder
    {
        return new TernaryFluentBuilder($this, whenTrue: $callback);
    }

    /**
     * Execute callback when state is FALSE.
     */
    public function whenFalse(callable $callback): TernaryFluentBuilder
    {
        return new TernaryFluentBuilder($this, whenFalse: $callback);
    }

    /**
     * Execute callback when state is UNKNOWN.
     */
    public function whenUnknown(callable $callback): TernaryFluentBuilder
    {
        return new TernaryFluentBuilder($this, whenUnknown: $callback);
    }

    /**
     * Transform state through a pipeline.
     * 
     * @example ternary($value)->pipe(fn($s) => $s->invert())->toBool()
     */
    public function pipe(callable $transformer): self
    {
        return $transformer($this);
    }

    /**
     * Convert to boolean with explicit handling.
     * 
     * @example ternary($value)->toBool(unknownAs: false)
     */
    public function toBool(bool $unknownAs = false): bool
    {
        return match ($this) {
            self::TRUE => true,
            self::FALSE => false,
            self::UNKNOWN => $unknownAs,
        };
    }

    /**
     * Match state to values - shorthand for pattern matching.
     * 
     * @example ternary($status)->match('Active', 'Inactive', 'Pending')
     */
    public function match(mixed $ifTrue, mixed $ifFalse, mixed $ifUnknown = null): mixed
    {
        return match ($this) {
            self::TRUE => $ifTrue,
            self::FALSE => $ifFalse,
            self::UNKNOWN => $ifUnknown ?? $ifFalse,
        };
    }

    private static function fromString(string $value): self
    {
        $normalized = Str::of($value)->trim()->lower()->value();

        return self::STRING_ALIASES[$normalized]
            ?? throw new InvalidArgumentException('Cannot derive ternary state from string value: ' . $value);
    }

    private static function fromInt(int $value): self
    {
        return self::INTEGER_ALIASES[$value]
            ?? ($value > 0 ? self::TRUE : ($value < 0 ? self::FALSE : self::UNKNOWN));
    }
}
