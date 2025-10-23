<?php

namespace VinkiusLabs\Trilean\Support;

use VinkiusLabs\Trilean\Collections\TernaryVector;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Services\TernaryLogicService;

class CircuitBuilder
{
    private array $gates = [];
    private array $inputs = [];

    public function __construct(private TernaryLogicService $logic) {}

    public function input(string $name, mixed $value): self
    {
        $this->inputs[$name] = $value;
        return $this;
    }

    public function gate(string $name, string $operator, array $operands, array $options = []): self
    {
        $this->gates[$name] = [
            'operator' => $operator,
            'operands' => $operands,
            'options' => $options,
        ];
        return $this;
    }

    public function and(string $name, array $operands): self
    {
        return $this->gate($name, 'and', $operands);
    }

    public function or(string $name, array $operands): self
    {
        return $this->gate($name, 'or', $operands);
    }

    public function maj(string $name, array $operands): self
    {
        return $this->gate($name, 'maj', $operands);
    }

    public function weighted(string $name, array $operands, array $weights): self
    {
        return $this->gate($name, 'weighted', $operands, ['weights' => $weights]);
    }

    public function evaluate(string $outputGate): TernaryState
    {
        $resolved = $this->inputs;

        foreach ($this->gates as $name => $gate) {
            $values = array_map(fn($op) => $resolved[$op] ?? $this->logic->normalise($op), $gate['operands']);

            $state = match ($gate['operator']) {
                'and' => $this->logic->and(...$values),
                'or' => $this->logic->or(...$values),
                'maj' => $this->logic->weighted($values, array_fill(0, count($values), 1)),
                'weighted' => $this->logic->weighted($values, $gate['options']['weights'] ?? []),
                default => TernaryState::UNKNOWN,
            };

            $resolved[$name] = $state;
        }

        return $resolved[$outputGate] ?? TernaryState::UNKNOWN;
    }

    public function toBlueprint(): array
    {
        return [
            'inputs' => $this->inputs,
            'gates' => collect($this->gates)->map(fn($g, $name) => [
                'operator' => $g['operator'],
                'operands' => $g['operands'],
                ...$g['options'],
            ])->all(),
            'output' => array_key_last($this->gates),
        ];
    }
}
