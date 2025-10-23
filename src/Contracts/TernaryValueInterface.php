<?php

namespace VinkiusLabs\Trilean\Contracts;

use VinkiusLabs\Trilean\Enums\TernaryState;

interface TernaryValueInterface
{
    public function getTernaryState(): TernaryState;

    public function setTernaryState(mixed $state): void;
}
