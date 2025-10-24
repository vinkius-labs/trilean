# 🔥 Trilean Global Helpers

> Comprehensive reference for the helper functions registered by the package so you can accelerate ternary workflows in Laravel.

## Overview
Global helpers expose frequent `TernaryLogicService` operations through ergonomic PHP functions. They simplify conditionals, normalization, and complex decisions while keeping code expressive and testable. All helpers are available once the `TernaryLogicServiceProvider` is registered.

## Quick Reference
| Helper | Signature | Returns | Primary Use |
| --- | --- | --- | --- |
| `ternary()` | `mixed $value, ?string $field = null` | `TernaryState\|TernaryFluentBuilder` | Consistent normalization + fluent API |
| `maybe()` | `mixed $value, array $callbacks = []` | `mixed` | Control flow without `if`|
| `trilean()` | `void` | `TernaryLogicService` | Resolve the core service |
| `ternary_vector()` | `iterable $values` | `TernaryVector` | Math/aggregation pipeline |
| `all_true()` | `mixed ...$values` | `bool` | AND gates |
| `any_true()` | `mixed ...$values` | `bool` | OR gates |
| `none_false()` | `mixed ...$values` | `bool` | Ensure no veto |
| `when_ternary()` | `mixed $value, array $callbacks` | `mixed` | Lazy, state-specific side effects |
| `consensus()` | `iterable $values, array $options = []` | `TernaryState` | Voting & quorum |
| `ternary_match()` | `mixed $value, array $map, mixed $default = null` | `mixed` | Human-friendly pattern matching |
| **🆕 `match_ternary()`** | `array $pattern, array $values` | `bool` | Pattern matching with wildcards |
| **🆕 `array_all_true()`** | `array $values` | `bool` | All array values must be true |
| **🆕 `array_any_true()`** | `array $values` | `bool` | At least one array value true |
| **🆕 `array_filter_true()`** | `array $values` | `array` | Keep only true values |
| **🆕 `array_filter_false()`** | `array $values` | `array` | Keep only false values |
| **🆕 `array_filter_unknown()`** | `array $values` | `array` | Keep only unknown values |
| **🆕 `array_count_ternary()`** | `array $values` | `array` | Count by state: `['true'=>N, 'false'=>N, 'unknown'=>N]` |
| **🆕 `ternary_coalesce()`** | `mixed ...$values` | `mixed` | First non-false ternary value |
| **🆕 `pipe_ternary()`** | `mixed $value, array $pipes` | `TernaryState` | Pipeline of ternary operations |
| **🆕 `decide()`** | `void` | `DecisionBuilder` | Fluent decision tree builder |
| **🆕 `gdpr_can_process()`** | `mixed $consent` | `bool` | GDPR: can process data? |
| **🆕 `gdpr_requires_action()`** | `mixed $consent` | `bool` | GDPR: needs consent action? |
| **🆕 `feature()`** | `mixed $flag, int $id, int $rollout` | `bool` | Feature flag with rollout |
| **🆕 `risk_level()`** | `int $score` | `string` | Risk level: 'low', 'medium', 'high' |
| **🆕 `fraud_score()`** | `int $score, int $threshold` | `TernaryState` | Fraud detection decision |
| **🆕 `compliant()`** | `string $strategy, array $checks` | `bool` | Multi-check compliance |
| **🆕 `approved()`** | `array $approvals` | `bool` | Multi-stakeholder approval |

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

---

## 🆕 New Helper Functions

### Pattern Matching

#### `match_ternary()`
- **Goal**: Match ternary value arrays against patterns with wildcard support.
- **Signature**: `function match_ternary(array $pattern, array $values): bool`
- **Pattern syntax**: Use `'*'` to ignore positions in the pattern.
- **Example**:
  ```php
  // Match first two checks, ignore third
  $matched = match_ternary([true, true, '*'], [$check1, $check2, $check3]);
  
  // Complex validation workflow
  $pattern = [true, true, '*', false]; // Must be true, true, any, false
  if (match_ternary($pattern, [$email, $phone, $address, $marketing])) {
      // Exact pattern matched
  }
  ```
- **Use cases**: Complex form validation, multi-step workflow verification, compliance checklists.

### Array Operations

#### `array_all_true()`
- **Goal**: Check if all array values are ternary TRUE.
- **Signature**: `function array_all_true(array $values): bool`
- **Example**:
  ```php
  if (array_all_true([$verified, $consented, $active])) {
      // All checks passed
  }
  ```

#### `array_any_true()`
- **Goal**: Check if at least one array value is TRUE.
- **Signature**: `function array_any_true(array $values): bool`
- **Example**:
  ```php
  if (array_any_true([$sms2fa, $email2fa, $app2fa])) {
      // At least one 2FA method enabled
  }
  ```

#### `array_filter_true()` / `array_filter_false()` / `array_filter_unknown()`
- **Goal**: Filter arrays by ternary state.
- **Signatures**: 
  - `function array_filter_true(array $values): array`
  - `function array_filter_false(array $values): array`
  - `function array_filter_unknown(array $values): array`
- **Example**:
  ```php
  $checks = ['email' => true, 'phone' => false, 'address' => null];
  
  $passed = array_filter_true($checks);    // ['email' => true]
  $failed = array_filter_false($checks);   // ['phone' => false]
  $pending = array_filter_unknown($checks); // ['address' => null]
  ```

#### `array_count_ternary()`
- **Goal**: Count ternary values by state.
- **Signature**: `function array_count_ternary(array $values): array`
- **Returns**: `['true' => int, 'false' => int, 'unknown' => int]`
- **Example**:
  ```php
  $stats = array_count_ternary($validationResults);
  // ['true' => 5, 'false' => 2, 'unknown' => 3]
  
  $passRate = $stats['true'] / array_sum($stats);
  ```

### Ternary Coalescing & Pipeline

#### `ternary_coalesce()`
- **Goal**: Return first non-FALSE ternary value (similar to null coalescing, but ternary-aware).
- **Signature**: `function ternary_coalesce(mixed ...$values): mixed`
- **Example**:
  ```php
  // Try primary, fallback to secondary, then default
  $consent = ternary_coalesce(
      $user->explicit_consent,
      $user->implied_consent,
      $company->default_consent,
      false  // Final fallback
  );
  ```

#### `pipe_ternary()`
- **Goal**: Pipeline value through multiple ternary operations.
- **Signature**: `function pipe_ternary(mixed $value, array $pipes): TernaryState`
- **Example**:
  ```php
  $result = pipe_ternary($email, [
      fn($v) => validateEmailFormat($v),
      fn($v) => checkDomainExists($v),
      fn($v) => verifyMXRecords($v),
      fn($v) => checkBlacklist($v),
  ]);
  
  if ($result->isTrue()) {
      // Email passed all pipeline checks
  }
  ```

### Decision Builder

#### `decide()`
- **Goal**: Create fluent decision trees without verbose arrays.
- **Signature**: `function decide(): DecisionBuilder`
- **Example**:
  ```php
  $approved = decide()
      ->input('verified', $user->email_verified)
      ->input('consent', $user->gdpr_consent)
      ->input('active', $subscription->active)
      ->and('verified', 'consent')
      ->or('verified', 'active')
      ->requireAll(['verified', 'consent'])
      ->toBool();
  
  // With full evaluation report
  $report = decide()
      ->input('check1', $value1)
      ->input('check2', $value2)
      ->consensus(['check1', 'check2'])
      ->evaluate();  // Returns DecisionReport
  ```

### Domain-Specific Helpers

#### GDPR & Privacy

##### `gdpr_can_process()`
- **Goal**: Check if data processing is allowed under GDPR.
- **Signature**: `function gdpr_can_process(mixed $consent): bool`
- **Returns**: TRUE only if consent is explicitly TRUE.
- **Example**:
  ```php
  if (gdpr_can_process($user->marketing_consent)) {
      sendMarketingEmail($user);
  }
  ```

##### `gdpr_requires_action()`
- **Goal**: Check if consent action is required (pending/unknown).
- **Signature**: `function gdpr_requires_action(mixed $consent): bool`
- **Example**:
  ```php
  if (gdpr_requires_action($user->data_consent)) {
      return redirect()->route('consent.request');
  }
  ```

#### Feature Flags

##### `feature()`
- **Goal**: Feature flag evaluation with gradual rollout.
- **Signature**: `function feature(mixed $flag, int $userId, int $rolloutPercentage = 0): bool`
- **Example**:
  ```php
  // TRUE = enabled, FALSE = disabled, UNKNOWN = gradual rollout
  if (feature($flags['new_ui'], $user->id, rolloutPercentage: 25)) {
      return view('app.new-ui');
  }
  
  // 100% rollout for unknown flags
  if (feature($flags['beta_feature'], $user->id, rolloutPercentage: 100)) {
      // All users in unknown state get the feature
  }
  ```

#### Risk & Fraud Detection

##### `risk_level()`
- **Goal**: Convert numeric risk score to level.
- **Signature**: `function risk_level(int $score): string`
- **Returns**: `'low'` (0-33), `'medium'` (34-66), or `'high'` (67-100).
- **Example**:
  ```php
  $level = risk_level($fraudScore);  // 'low', 'medium', 'high'
  
  if ($level === 'high') {
      flagTransaction($transaction);
  }
  ```

##### `fraud_score()`
- **Goal**: Ternary decision based on fraud threshold.
- **Signature**: `function fraud_score(int $score, int $threshold = 70): TernaryState`
- **Returns**: 
  - TRUE if score >= threshold (fraudulent)
  - FALSE if score < threshold - 30 (safe)
  - UNKNOWN if in between (needs review)
- **Example**:
  ```php
  $decision = fraud_score($transactionScore, threshold: 75);
  
  return ternary($decision)
      ->ifTrue('REJECT')
      ->ifFalse('APPROVE')
      ->ifUnknown('MANUAL_REVIEW')
      ->resolve();
  ```

#### Compliance & Approvals

##### `compliant()`
- **Goal**: Check compliance using different strategies.
- **Signature**: `function compliant(string $strategy, array $checks): bool`
- **Strategies**: `'strict'`, `'lenient'`, `'majority'`
- **Example**:
  ```php
  // All must be true
  if (compliant('strict', [$legal, $finance, $security])) {
      processTransaction();
  }
  
  // None can be false (unknown OK)
  if (compliant('lenient', $approvals)) {
      saveDraft();
  }
  
  // More true than false
  if (compliant('majority', $votes)) {
      implementFeature();
  }
  ```

##### `approved()`
- **Goal**: Multi-stakeholder approval with department checks.
- **Signature**: `function approved(array $approvals): bool`
- **Example**:
  ```php
  $approved = approved([
      'legal' => $document->legal_approval,
      'finance' => $document->finance_approval,
      'executive' => $document->executive_approval,
  ]);
  ```

> Use helpers as your foundational layer—they eliminate divergence and accelerate every other feature in the Trilean ecosystem.
