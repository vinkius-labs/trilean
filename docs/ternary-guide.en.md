# 📘 Trilean Guide (English)

## Overview
Trilean brings ternary computing to Laravel. Every decision embraces `TRUE`, `FALSE`, and `UNKNOWN`, eliminating surprises caused by nullable/source-of-truth mismatches.

## Before vs After
### Scenario: Enabling a premium module
**Before (booleans only)**
```php
if ($user->verified && $user->consent && ! $user->blocked) {
    return 'enabled';
}

return 'denied';
```

**After (Trilean)**
```php
if (all_true($user->verified, $user->consent, ! $user->blocked)) {
    return 'enabled';
}

return ternary_match(false, [
    'true' => 'enabled',
    'false' => 'denied',
    'unknown' => 'needs review',
]);
```
`UNKNOWN` is now a first-class state instead of a hidden bug.

### Scenario: Approval workflow
**Before**
```php
if (! $doc->legal_approved) {
    return 'legal pending';
}

if (! $doc->finance_approved) {
    return 'finance pending';
}

return 'published';
```

**After**
```php
$state = collect([
    $doc->legal_approved,
    $doc->finance_approved,
    $doc->manager_approved,
])->ternaryWeighted([5, 3, 2]);

return ternary_match($state, [
    'true' => 'published',
    'false' => 'rejected',
    'unknown' => 'in review',
]);
```
Weighted decisions and interim states are expressed explicitly.

## Technical Highlights
### 1. 🔥 Global Helpers (10 functions)
- `ternary()` – Normalizes mixed values through `TernaryState::fromMixed`.
- `maybe()` – Lazy ternary branching with callbacks per outcome.
- `trilean()` – Resolves `TernaryLogicService` from the container.
- `ternary_vector()` – Wraps arrays in `TernaryVector` for math operations.
- `all_true()` – Applies `TernaryLogicService::and` and returns boolean.
- `any_true()` – Evaluates ternary OR via `TernaryLogicService::or`.
- `none_false()` – Ensures no `FALSE` by combining OR+AND gates.
- `when_ternary()` – Executes closures based on the state.
- `consensus()` – Uses `TernaryLogicService::consensus` to resolve votes.
- `ternary_match()` – Developer-friendly pattern matching.

### 2. 💎 Collection Macros (12 methods)
- `ternaryConsensus()` / `ternaryMajority()` – Powered by `TernaryVector`.
- `whereTernaryTrue/False/Unknown()` – Filters using helper normalization.
- `ternaryWeighted()` – Delegates to `trilean()->weighted`.
- `ternaryMap()` – Returns a `TernaryVector` for continued math.
- `ternaryScore()` – Balanced score (+1, 0, -1) over the collection.
- `allTernaryTrue()` / `anyTernaryTrue()` – Convenience logical gates.
- `partitionTernary()` – Splits the collection into three subsets.
- `ternaryGate()` – Flexible AND/OR/XOR/consensus via collections.

### 3. 🗄️ Eloquent Scopes (8 methods)
- `whereTernaryTrue/False/Unknown()` – Maps states to portable SQL.
- `orderByTernary()` – Uses a CASE expression to prefer `TRUE` values.
- `whereAllTernaryTrue()` / `whereAnyTernaryTrue()` – Compose across columns.
- `ternaryConsensus()` – Resolves combined decisions over fetched models.

### 4. 🌐 Request Macros (5 methods)
- `ternary()` – Normalizes request input.
- `hasTernaryTrue/False/Unknown()` – Quick validation helpers.
- `ternaryGate()` – Evaluates multiple keys with AND/OR/consensus.
- `ternaryExpression()` – Exposes the ternary DSL directly on the request.

### 5. 🎨 Blade Directives (10+)
- `@ternary`, `@ternaryTrue/False/Unknown` – Expressive conditionals.
- `@maybe` – Inline rendering of ternary states.
- `@ternaryMatch` + `@case` – Pattern matching in templates.
- `@ternaryBadge` / `@ternaryIcon` – Consistent UI fragments.
- `@allTrue` / `@anyTrue` – Multi-check gating in views.

### 6. 🛡️ Middleware
- `TernaryGateMiddleware` – Evaluates user/request attributes before continuing.
- `RequireTernaryTrue` – Blocks unless the attribute resolves to `TRUE`.

### 7. ✅ Validation Rules
- Unit: `ternary`, `ternary_true`, `ternary_not_false`.
- Group: `ternary_gate`, `ternary_any_true`, `ternary_all_true`, `ternary_consensus`.
- Advanced: `ternary_weighted`, `ternary_expression`.

### 8. 🧮 Advanced Features
- `TernaryArithmetic` – Balanced arithmetic with carry handling.
- `CircuitBuilder` – Fluent ternary circuits with visualization/export hooks.
- Balanced trit converter with unicode and extended alias support.

## Detailed Use Cases
1. **Feature Flags** – Combine `maybe()` and `ternaryWeighted()` for resilient toggles.
2. **Workflows** – Keep progress visible without blocking users via `UNKNOWN` states.
3. **Health Checks** – `ternaryMajority()` highlights degradation vs total outage.

## Future Ideas To Delight Developers
- **Ternary cache** keyed by state to avoid recompute.
- **Real-time ternary monitor** (UI + webhooks) for decision streams.
- **Automatic policies** wired into `Gate` with UNKNOWN fallbacks.
- **Decision replay** using encoded vectors for auditing.
- **Artisan inspector** to diagnose routes and inputs with ternary semantics.

---
These tools deliver clarity, velocity, and observability to every mission-critical Laravel decision.
