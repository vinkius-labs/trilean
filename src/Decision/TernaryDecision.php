<?php

namespace VinkiusLabs\Trilean\Decision;

use VinkiusLabs\Trilean\Enums\TernaryState;

final class TernaryDecision
{
    /**
     * @param array<int|string, mixed> $evidence
     */
    public function __construct(
        public readonly string $name,
        public readonly TernaryState $state,
        public readonly string $operator,
        public readonly array $evidence = [],
        public readonly ?string $description = null,
    ) {}
}
