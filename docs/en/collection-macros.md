# 💎 Trilean Collection Macros (12 methods)

> Extend `Illuminate\Support\Collection` with declarative ternary operations that keep pipelines legible, fast, and auditable.

## Overview
Macros are registered by the `TernaryLogicServiceProvider` during boot and become available on every `Collection` (and `LazyCollection` when compatible). They sit on top of global helpers and the `TernaryLogicService`, guaranteeing consistency across controllers, jobs, and data pipelines.

## Reference Table
| Macro | Return | Purpose |
| --- | --- | --- |
| `ternaryConsensus()` | `TernaryState` | Unify votes/states |
| `ternaryMajority()` | `TernaryState` | Decide by simple majority |
| `whereTernaryTrue()` | `Collection` | Filter items marked `TRUE` |
| `whereTernaryFalse()` | `Collection` | Filter items marked `FALSE` |
| `whereTernaryUnknown()` | `Collection` | Filter items marked `UNKNOWN` |
| `ternaryWeighted(array $weights)` | `TernaryState` | Weighted decisions |
| `ternaryMap(callable $callback)` | `TernaryVector` | Normalize transforms |
| `ternaryScore()` | `int` | Balanced metric (+1/0/-1) |
| `allTernaryTrue()` | `bool` | AND gate |
| `anyTernaryTrue()` | `bool` | OR gate |
| `partitionTernary()` | `array<Collection>` | Split into 3 sub-collections |
| `ternaryGate(array $options)` | `TernaryState` | AND/OR/XOR/consensus/weighted |

## Macro Breakdown

### 1. `ternaryConsensus()`
- Normalizes each item via `ternary()` and uses `TernaryLogicService::consensus`.
- Ideal for approval boards, multi-signal health checks.
- Combine with `mapWithKeys` first to preserve identifiers.

### 2. `ternaryMajority()`
- Shortcut for majority vote (no weights). Ties resolve to `UNKNOWN`.
- Great for distributed clusters/failover decisions.

### 3–5. `whereTernary*()`
- Use `data_get` + `ternary()` behind the scenes.
- Signature: `whereTernaryTrue(string $key)` (mirrored for `false`/`unknown`).
- Keeps filters readable while handling arrays or objects uniformly.

### 6. `ternaryWeighted(array $weights)`
- Resolves weighted decisions; accepts associative arrays for clarity.
- Defaults missing weights to `1`.
- Returns `TernaryDecisionReport` when `['report' => true]` supplied.

### 7. `ternaryMap(callable $callback)`
- Works like `map` but coerces results into a `TernaryVector`.
- Enables chaining `sum`, `weighted`, `encode` without leaving ternary context.

### 8. `ternaryScore()`
- Translates states to +1 (`TRUE`), 0 (`UNKNOWN`), -1 (`FALSE`) and returns total.
- Useful for dashboards, anomaly scoring, threshold comparisons.

### 9–10. `allTernaryTrue()` / `anyTernaryTrue()`
- Operate on the entire collection.
- Accept optional callback to extract states: `$collection->allTernaryTrue(fn ($item) => $item->state)`.

### 11. `partitionTernary()`
- Returns an array with `'true'`, `'false'`, `'unknown'` keys, each a `Collection`.
- Perfect for dashboards, exports, or incremental reprocessing.

### 12. `ternaryGate(array $options = [])`
- Applies configurable operators (`and`, `or`, `xor`, `consensus`, `weighted`).
- Accepts closures receiving a `TernaryVector` for custom logic.
- Example:
  ```php
  $state = $signals->ternaryGate('weighted', [
      'weights' => [5, 3, 2],
      'requiredRatio' => 0.6,
  ]);
  ```

## Recommended Patterns
- **Data pipelines**: `map -> ternaryMap -> ternaryGate` keeps logic declarative.
- **Aggregate roots**: expose `Collection` + macros to compose decisions in DDD aggregates.
- **Jobs**: serialize snapshots via `ternaryMap()->encoded()` to replay decisions.

## Best Practices
- Normalize external data before applying macros.
- Document keys used in `whereTernary*` for onboarding.
- Avoid mixing multiple operator types in a single `ternaryGate` call; extract helpers instead.

## Testing
- Use `collect([...])` with explicit states (`TernaryState::true()` etc.) for assertions.
- For keyed macros, rely on DTOs or raw arrays to mimic payloads.
- Combine `tap` with snapshots on `ternaryMap` to confirm normalization runs.

## Observability
- Prepend `map(fn ($item) => [$item->id, ternary($item->state)])` before gating to improve logs.
- Export `ternaryMap()->toBits()` to analyze regressions in load tests.

> Collection macros convert collections into miniature DSLs, preserving clarity even when rules and states scale dramatically.
