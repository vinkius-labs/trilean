<?php

namespace VinkiusLabs\Trilean\Decision;

use Illuminate\Support\Collection;
use VinkiusLabs\Trilean\Enums\TernaryState;

final class TernaryDecisionReport
{
    /**
     * @param Collection<int, TernaryDecision> $decisions
     */
    public function __construct(
        private readonly TernaryState $result,
        private readonly Collection $decisions,
        private readonly string $encodedVector,
        private readonly array $metadata = [],
    ) {}

    public function result(): TernaryState
    {
        return $this->result;
    }

    /**
     * @return Collection<int, TernaryDecision>
     */
    public function decisions(): Collection
    {
        return $this->decisions;
    }

    public function toArray(): array
    {
        return [
            'result' => $this->result->value,
            'encoded' => $this->encodedVector,
            'decisions' => $this->decisions->map(fn(TernaryDecision $decision) => [
                'name' => $decision->name,
                'operator' => $decision->operator,
                'state' => $decision->state->value,
                'description' => $decision->description,
                'evidence' => $decision->evidence,
            ])->all(),
            'metadata' => $this->metadata,
        ];
    }

    public function encodedVector(): string
    {
        return $this->encodedVector;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }
}
