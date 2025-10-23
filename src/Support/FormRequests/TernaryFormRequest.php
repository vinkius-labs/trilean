<?php

namespace VinkiusLabs\Trilean\Support\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Services\TernaryLogicService;
use function collect;

abstract class TernaryFormRequest extends FormRequest
{
    public function ternary(string $key, mixed $default = null): TernaryState
    {
        $value = $this->validated()[$key] ?? $this->input($key, $default);

        return ternary($value);
    }

    public function ternaryAny(array $keys): bool
    {
        $values = collect($keys)->map(fn(string $key) => $this->input($key))->all();

        return any_true(...$values);
    }

    public function ternaryAll(array $keys): bool
    {
        $values = collect($keys)->map(fn(string $key) => $this->input($key))->all();

        return all_true(...$values);
    }

    public function ternaryGate(array $keys, array $options = []): TernaryState
    {
        $logic = app(TernaryLogicService::class);
        $values = collect($keys)->map(fn(string $key) => $this->input($key))->all();
        $operator = $options['operator'] ?? 'and';

        return match ($operator) {
            'and' => $logic->and(...$values),
            'or' => $logic->or(...$values),
            'xor' => $logic->xor(...$values),
            'consensus' => $logic->consensus($values),
            'weighted' => $logic->weighted($values, $options['weights'] ?? []),
            default => $logic->and(...$values),
        };
    }
}
