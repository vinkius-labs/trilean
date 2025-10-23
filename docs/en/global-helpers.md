# 🔥 Trilean Global Helpers

> Comprehensive reference for the helper functions registered by the package so you can accelerate ternary workflows in Laravel.

## Overview
Global helpers expose frequent `TernaryLogicService` operations through ergonomic PHP functions. They simplify conditionals, normalization, and complex decisions while keeping code expressive and testable. All helpers are available once the `TernaryLogicServiceProvider` is registered.

## Quick Reference
| Helper | Signature | Returns | Primary Use |
| --- | --- | --- | --- |
| `ternary()` | `mixed $value, ?string $field = null` | `TernaryState` | Consistent normalization |
| `maybe()` | `mixed $value, array $callbacks = []` | `mixed` | Control flow without `if`|
| `trilean()` | `void` | `TernaryLogicService` | Resolve the core service |
| `ternary_vector()` | `iterable $values` | `TernaryVector` | Math/aggregation pipeline |
| `all_true()` | `mixed ...$values` | `bool` | AND gates |
| `any_true()` | `mixed ...$values` | `bool` | OR gates |
| `none_false()` | `mixed ...$values` | `bool` | Ensure no veto |
| `when_ternary()` | `mixed $value, array $callbacks` | `mixed` | Lazy, state-specific side effects |
| `consensus()` | `iterable $values, array $options = []` | `TernaryState` | Voting & quorum |
| `ternary_match()` | `mixed $value, array $map, mixed $default = null` | `mixed` | Human-friendly pattern matching |

## Helper Details

### `ternary()`
- **Goal**: Convert any value into a `TernaryState` (`true`, `false`, `unknown`).
- **Actual signature**: `function ternary(mixed $value, ?string $field = null, array $context = []): TernaryState`
- **How it works**: Delegates to `TernaryState::fromMixed`, applying heuristics for booleans, integers, strings, `null`, enums, and Eloquent attributes.
- **When to use**:
  - Normalize form inputs before persistence.
  - Build Collection pipelines with consistent semantics.
  - Serialize ternary data in logs.
- **Example**:
  ```php
  $state = ternary($request->input('risk_level'));

  if ($state->isUnknown()) {
      return response()->json(['status' => 'awaiting-data']);
  }
  ```
- **Best practices**:
  - Provide `$field` to generate friendly error messages.
  - Combine with `data_get` for nested values.
  - Document expected contracts for teammates.

### `maybe()`
- **Goal**: Declarative ternary branching without sprawling `if/else` blocks.
- **Signature**: `function maybe(mixed $value, array $callbacks = [], mixed $fallback = null)`
- **Callback keys**: `'true'`, `'false'`, `'unknown'` (required). Optional `'any'` and `'default'` for post-processing and fallbacks.
- **Example**:
  ```php
  return maybe($featureFlag, [
      'true' => fn () => $this->enablePremium(),
      'false' => fn () => $this->logSkip('flag disabled'),
      'unknown' => fn () => $this->queueReview(),
      'any' => fn ($state) => Metrics::record('flags.checked', $state->name),
  ]);
  ```
- **Notes**:
  - Callbacks execute lazily; avoid side effects outside them.
  - Ensure return types stay coherent across callbacks.
  - Pair with decision reports for observability.

### `trilean()`
- **Goal**: Resolve the main service without manual container boilerplate.
- **Signature**: `function trilean(): TernaryLogicService`
- **Common use**: Run advanced operations (`xor`, `weighted`, `consensus`) or mock the binding in tests.

### `ternary_vector()`
- **Goal**: Wrap ternary collections with math-ready APIs.
- **Signature**: `function ternary_vector(iterable $values, array $options = []): TernaryVector`
- **Capabilities**: `sum()`, `average()`, `majority()`, `weighted()`, `encode()`.
- **Example**:
  ```php
  $vector = ternary_vector([$sensorA, $sensorB, $sensorC]);

  if ($vector->majority()->isTrue()) {
      dispatch(new ActivateFailover);
  }
  ```
- **Warnings**: Normalize heterogeneous sources before feeding them; prefer associative weights for readability.

### `all_true()`
- **Goal**: Ternary-aware AND shortcut.
- **Behavior**: Returns `false` if any operand resolves to `FALSE` or `UNKNOWN`.
- **Example**:
  ```php
  if (all_true($user->verified, $user->hasTwoFactorEnabled(), ! $user->blocked)) {
      // release feature
  }
  ```

### `any_true()`
- **Goal**: Ternary-aware OR.
- **Insight**: Returns `true` as soon as one operand is `TRUE`; returns `false` if all are `UNKNOWN` for safer defaults.

### `none_false()`
- **Goal**: Ensure no stakeholder vetoed the decision.
- **Example**:
  ```php
  if (none_false($policy->legal, $policy->compliance, $policy->security)) {
      Approvals::record($policy);
  }
  ```
- **Why**: Allows uncertainty but blocks explicit failure.

### `when_ternary()`
- **Goal**: Centralize side effects per state with fallback.
- **Use case**:
  ```php
  when_ternary($deploymentStatus, [
      'true' => fn () => Notifier::success('Deploy stable'),
      'false' => fn () => Notifier::critical('Rollback required'),
      'unknown' => fn () => Notifier::warning('Monitoring'),
  ]);
  ```

### `consensus()`
- **Goal**: Resolve voting/quorum scenarios with optional tie-breakers.
- **Options**: `requiredRatio`, `weights`, `tieBreakers`.
- **Example**:
  ```php
  $decision = consensus([
      'legal' => $doc->legal_state,
      'finance' => $doc->finance_state,
      'ops' => $doc->ops_state,
  ], options: [
      'weights' => ['legal' => 3, 'finance' => 2, 'ops' => 1],
      'requiredRatio' => 0.66,
  ]);
  ```

### `ternary_match()`
- **Goal**: Map states to friendly outputs—labels, responses, UI pieces.
- **Example**:
  ```php
  $label = ternary_match($device->health_state, [
      'true' => __('device.status.ok'),
      'false' => __('device.status.down'),
      'unknown' => __('device.status.degraded'),
  ]);
  ```
- **Pro tip**: Accepts closures in the map, and optional `'any'` callback for post-processing.

## Combined Strategies
- **Feature flags**: `when_ternary()` for side effects, `any_true()` for fallbacks, `ternary_match()` for UI.
- **BFF/API**: Normalize request payloads with `ternary()`, aggregate with `consensus()` before responding.
- **Logging & metrics**: Feed helpers into `TernaryDecisionReport` for standardized outputs.

## Testing & Debugging
- Mock the service via `app()->instance(TernaryLogicService::class, $fake)`.
- Leverage `TernaryState::true() / ::false() / ::unknown()` for explicit assertions.
- Capture events or reports when validating side effects.

## Adoption Checklist
- [ ] Replaced fragile conditionals with helper equivalents?
- [ ] Documented expected ternary inputs for the team?
- [ ] Instrumented decisions with metrics/logs?
- [ ] Added test cases covering `UNKNOWN` pathways?

> Use helpers as your foundational layer—they eliminate divergence and accelerate every other feature in the Trilean ecosystem.
