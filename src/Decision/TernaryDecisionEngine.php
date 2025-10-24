<?php

namespace VinkiusLabs\Trilean\Decision;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use VinkiusLabs\Trilean\Events\TernaryDecisionEvaluated;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Services\TernaryLogicService;

class TernaryDecisionEngine
{
    private bool $memoizeEnabled = false;
    private static array $cache = [];

    public function __construct(private readonly TernaryLogicService $logic) {}

    /**
     * Enable memoization for this engine instance.
     */
    public function memoize(bool $enabled = true): self
    {
        $this->memoizeEnabled = $enabled;
        return $this;
    }

    /**
     * Clear all memoized decisions.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * @param array<string, mixed> $blueprint
     * @param array<string, mixed> $context
     */
    public function evaluate(array $blueprint, array $context = []): TernaryDecisionReport
    {
        // Check cache if memoization is enabled
        if ($this->memoizeEnabled || config('trilean.cache.enabled', false)) {
            $cacheKey = $this->getCacheKey($blueprint, $context);

            if (isset(self::$cache[$cacheKey])) {
                $cached = self::$cache[$cacheKey];

                // Check TTL if configured
                $ttl = config('trilean.cache.ttl', 3600);
                if ($cached['expires_at'] > time()) {
                    return $cached['report'];
                }

                // Expired - remove from cache
                unset(self::$cache[$cacheKey]);
            }
        }

        $startedAt = microtime(true);
        $inputs = $this->resolveInputs($blueprint['inputs'] ?? [], $context);
        $decisions = Collection::make();

        foreach ($blueprint['gates'] ?? [] as $name => $gate) {
            $operator = strtolower($gate['operator'] ?? 'and');
            $operands = $gate['operands'] ?? [];
            $description = $gate['description'] ?? null;

            $values = $this->resolveOperands($operands, $inputs, $decisions);

            $expressionContext = array_merge($context, $inputs->toArray());

            $state = match ($operator) {
                'and' => $this->logic->and(...$values),
                'or' => $this->logic->or(...$values),
                'not' => $this->logic->not($values[0] ?? TernaryState::UNKNOWN),
                'consensus' => $this->logic->consensus($values),
                'weighted' => $this->logic->weighted($values, $gate['weights'] ?? []),
                'expression' => $this->logic->expression($gate['expression'] ?? '', $expressionContext),
                default => $this->logic->and(...$values),
            };

            $evidence = Collection::make($values)->map(fn($value, $index) => [
                'operand' => $operands[$index] ?? $index,
                'state' => TernaryState::fromMixed($value)->value,
            ])->all();

            $decision = new TernaryDecision(
                name: is_string($name) ? $name : 'gate_' . $name,
                state: $state,
                operator: strtoupper($operator),
                evidence: $evidence,
                description: $description,
            );

            $inputs[$decision->name] = $decision->state;
            $decisions->push($decision);
        }

        $outputKey = $blueprint['output'] ?? ($decisions->last()?->name ?? 'result');
        $resultState = TernaryState::fromMixed($inputs[$outputKey] ?? $decisions->last()?->state ?? TernaryState::UNKNOWN);

        $encoded = $this->logic->encode($decisions->map(fn(TernaryDecision $decision) => $decision->state));

        $metadata = [
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 3),
            'total_gates' => $decisions->count(),
            'blueprint' => $blueprint['name'] ?? $blueprint['id'] ?? null,
        ];

        $report = new TernaryDecisionReport($resultState, $decisions, $encoded, $metadata);

        if (config('trilean.metrics.enabled', false)) {
            event(new TernaryDecisionEvaluated($report, $context, $blueprint));
        }

        // Cache the result if memoization is enabled
        if ($this->memoizeEnabled || config('trilean.cache.enabled', false)) {
            $cacheKey = $this->getCacheKey($blueprint, $context);
            $ttl = config('trilean.cache.ttl', 3600);

            self::$cache[$cacheKey] = [
                'report' => $report,
                'expires_at' => time() + $ttl,
                'cached_at' => time(),
            ];
        }

        return $report;
    }

    /**
     * Generate cache key from blueprint and context.
     */
    private function getCacheKey(array $blueprint, array $context): string
    {
        $data = [
            'blueprint' => $blueprint,
            'context' => $context,
        ];

        return md5(serialize($data));
    }

    private function resolveInputs(array $inputs, array $context): Collection
    {
        return Collection::make($inputs)
            ->map(fn($value) => $this->resolveValue($value, $context));
    }

    private function resolveOperands(array $operands, Collection $inputs, Collection $decisions): array
    {
        return Collection::make($operands)
            ->map(function ($operand) use ($inputs, $decisions) {
                if (is_callable($operand)) {
                    return $this->logic->normalise($operand($inputs, $decisions));
                }

                if (is_string($operand)) {
                    if (str_starts_with($operand, '!')) {
                        $resolved = $this->resolveKey(substr($operand, 1), $inputs, $decisions);

                        return $resolved->invert();
                    }

                    if (str_starts_with($operand, '@')) {
                        $expression = substr($operand, 1);

                        return $this->logic->expression($expression, $inputs->all());
                    }

                    return $this->resolveKey($operand, $inputs, $decisions);
                }

                return $this->logic->normalise($operand);
            })
            ->all();
    }

    private function resolveKey(string $key, Collection $inputs, Collection $decisions): TernaryState
    {
        if ($inputs->has($key)) {
            return TernaryState::fromMixed($inputs->get($key));
        }

        $decision = $decisions->firstWhere('name', $key);
        if ($decision instanceof TernaryDecision) {
            return $decision->state;
        }

        throw new \Exception("Undefined operand: {$key}");
    }

    private function resolveValue(mixed $value, array $context): TernaryState
    {
        if ($value instanceof TernaryState) {
            return $value;
        }

        if (is_callable($value)) {
            return $this->logic->normalise($value($context));
        }

        if (is_string($value)) {
            if (str_starts_with($value, '@')) {
                $expression = substr($value, 1);

                return $this->logic->expression($expression, $context);
            }

            return TernaryState::fromMixed(Arr::get($context, $value));
        }

        return $this->logic->normalise($value);
    }
}
