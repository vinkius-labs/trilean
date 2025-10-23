<?php

namespace VinkiusLabs\Trilean\Services;

use Illuminate\Support\Collection;
use VinkiusLabs\Trilean\Enums\TernaryState;

class TernaryExpressionEvaluator
{
    private array $operators = [
        '!' => ['precedence' => 3, 'arity' => 1],
        'NOT' => ['precedence' => 3, 'arity' => 1],
        '&' => ['precedence' => 2, 'arity' => 2],
        'AND' => ['precedence' => 2, 'arity' => 2],
        '|' => ['precedence' => 1, 'arity' => 2],
        'OR' => ['precedence' => 1, 'arity' => 2],
        '^' => ['precedence' => 1, 'arity' => 2], // ternary XOR (majority false implies unknown)
        'MAJ' => ['precedence' => 1, 'arity' => 2], // majority vote
        'CONSENSUS' => ['precedence' => 1, 'arity' => 2], // consensus
        'IF' => ['precedence' => 0, 'arity' => 3], // ternary if-then-else
    ];

    public function evaluate(string $expression, array $context, TernaryLogicService $logic): TernaryState
    {
        $tokens = $this->tokenise($expression);
        $output = [];
        $operators = [];

        foreach ($tokens as $token) {
            if ($this->isOperator($token)) {
                $tokenKey = $this->normaliseOperator($token);
                $tokenPrecedence = $this->operators[$tokenKey]['precedence'];

                while (!empty($operators)) {
                    $peek = end($operators);

                    if ($peek === '(') {
                        break;
                    }

                    $peekKey = $this->normaliseOperator($peek);
                    if ($tokenPrecedence <= $this->operators[$peekKey]['precedence']) {
                        $output[] = array_pop($operators);
                        continue;
                    }

                    break;
                }

                $operators[] = $tokenKey;
                continue;
            }

            if ($token === '(') {
                $operators[] = $token;
                continue;
            }

            if ($token === ')') {
                while (!empty($operators) && end($operators) !== '(') {
                    $output[] = array_pop($operators);
                }
                array_pop($operators);
                continue;
            }

            $output[] = $token;
        }

        while (!empty($operators)) {
            $output[] = array_pop($operators);
        }

        $stack = [];
        foreach ($output as $token) {
            if ($this->isOperator($token)) {
                $operatorKey = $this->normaliseOperator($token);
                $arity = $this->operators[$operatorKey]['arity'];

                $operands = [];
                for ($i = 0; $i < $arity; $i++) {
                    $operands[] = array_pop($stack);
                }

                $result = match ($operatorKey) {
                    '!', 'NOT' => $logic->not($this->resolveValue(array_pop($operands), $context)),
                    '&', 'AND' => $logic->and(...$this->resolveValues($operands, $context)),
                    '|', 'OR' => $logic->or(...$this->resolveValues($operands, $context)),
                    '^' => $logic->xor(...$this->resolveValues($operands, $context)),
                    'MAJ' => $logic->weighted($this->resolveValues($operands, $context), array_fill(0, count($operands), 1)),
                    'CONSENSUS' => $logic->consensus($this->resolveValues($operands, $context)),
                    'IF' => $this->evaluateIf($operands, $context, $logic),
                    default => $this->evaluateCustom($operatorKey, $operands, $context, $logic),
                };

                $stack[] = $result;
                continue;
            }

            $stack[] = $this->resolveValue($token, $context);
        }

        return $logic->normalise(array_pop($stack));
    }

    private function tokenise(string $expression): array
    {
        $operatorPattern = implode('|', array_map('preg_quote', array_keys($this->operators)));
        $pattern = "/\s*(\(|\)|{$operatorPattern})\s*/i";
        $parts = preg_split($pattern, $expression, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter(array_map('trim', $parts), fn($part) => $part !== ''));
    }

    private function isOperator(string $token): bool
    {
        $normalised = $this->normaliseOperator($token);

        return isset($this->operators[$normalised]);
    }

    private function normaliseOperator(string $token): string
    {
        $upper = strtoupper($token);

        return match ($upper) {
            '!' => '!',
            'AND', '&' => 'AND',
            'OR', '|' => 'OR',
            'NOT' => 'NOT',
            '^', 'XOR' => '^',
            'MAJ', 'MAJORITY' => 'MAJ',
            'CONSENSUS' => 'CONSENSUS',
            'IF', 'IFTHENELSE' => 'IF',
            default => $upper,
        };
    }

    private function resolveValues(array $operands, array $context): array
    {
        return Collection::make($operands)
            ->reverse()
            ->map(fn($operand) => $this->resolveValue($operand, $context))
            ->all();
    }

    private function resolveValue(mixed $value, array $context): TernaryState
    {
        if ($value instanceof TernaryState) {
            return $value;
        }

        if (is_string($value)) {
            $key = strtolower($value);

            return match ($key) {
                'true' => TernaryState::TRUE,
                'false' => TernaryState::FALSE,
                'unknown' => TernaryState::UNKNOWN,
                default => $this->fetchFromContext($value, $context),
            };
        }

        return TernaryState::fromMixed($value);
    }

    private function fetchFromContext(string $key, array $context): TernaryState
    {
        if (str_starts_with($key, '!')) {
            $value = $this->fetchFromContext(substr($key, 1), $context);

            return $value->invert();
        }

        $segments = explode('.', $key);
        $cursor = $context;

        foreach ($segments as $segment) {
            if (is_array($cursor) && array_key_exists($segment, $cursor)) {
                $cursor = $cursor[$segment];
                continue;
            }

            if (is_object($cursor) && isset($cursor->{$segment})) {
                $cursor = $cursor->{$segment};
                continue;
            }

            return TernaryState::UNKNOWN;
        }

        return TernaryState::fromMixed($cursor);
    }

    private function evaluateIf(array $operands, array $context, TernaryLogicService $logic): TernaryState
    {
        // operands[2] = condition, [1] = then, [0] = else
        $condition = $this->resolveValue($operands[2], $context);
        $then = $this->resolveValue($operands[1], $context);
        $else = $this->resolveValue($operands[0], $context);

        return match ($condition) {
            TernaryState::TRUE => $then,
            TernaryState::FALSE => $else,
            TernaryState::UNKNOWN => $logic->consensus([$then, $else]),
        };
    }

    private function evaluateCustom(string $operatorKey, array $operands, array $context, TernaryLogicService $logic): TernaryState
    {
        if (!isset($this->operators[$operatorKey]['handler'])) {
            return TernaryState::UNKNOWN;
        }

        $handler = $this->operators[$operatorKey]['handler'];
        $values = $this->resolveValues($operands, $context);

        return $handler($values, $logic);
    }
}
