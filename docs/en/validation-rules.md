# ✅ Ternary Validation Rules

> Enforce input integrity when accepting and combining ternary states across forms, APIs, and events.

## Overview
Rules are registered via `Validator::extend` inside the service provider. Each rule name maps to a closure that uses `TernaryLogicService` and global helpers. Apply them in `FormRequest`, manual validation (`Validator::make`), or `Rule::` definitions.

## Rules
| Rule | Type | Description |
| --- | --- | --- |
| `ternary` | Single field | Value must be convertible to `TernaryState` |
| `ternary_true` | Single field | Value must resolve to `TRUE` |
| `ternary_not_false` | Single field | Value must not resolve to `FALSE` |
| `ternary_gate` | Multi field | Apply operator (`and`, `or`, `xor`, `consensus`) |
| `ternary_any_true` | Multi field | At least one field `TRUE` |
| `ternary_all_true` | Multi field | All fields `TRUE` |
| `ternary_consensus` | Multi field | Custom voting logic |
| `ternary_weighted` | Multi field | Weighted decision with thresholds |
| `ternary_expression` | Multi field | Evaluate ternary DSL expression |

## Basic Usage
```php
$request->validate([
    'kyc_state' => ['required', 'ternary'],
    'aml_state' => ['required', 'ternary_not_false'],
]);
```

## Multi-Field Rules
```php
$request->validate([
    'checks' => ['required', 'array'],
    'checks.*' => ['ternary'],
    'checks' => ['ternary_gate:and'],
]);
```
- **Syntax**: `ternary_gate:operator,options...`
- **Options**: `requiredRatio=0.66`, `weights=legal:5,finance:3`, `report=true`.

### `ternary_expression`
```php
$request->validate([
    'decision' => ['required', 'ternary_expression:kyc && (aml || override)'],
]);
```
- Expression evaluates using request fields (`kyc`, `aml`, `override`).
- `null` values become `UNKNOWN`; validation fails depending on your context.

## Custom Messages
Add translations to `lang/en/validation.php`:
```php
'ternary' => 'The :attribute must be true, false, or unknown.',
'ternary_gate' => 'The ternary combination of :attribute did not meet the expected threshold.',
```

## Best Practices
- Always combine `ternary` / `ternary_not_false` to guarantee clean inputs before using macros.
- For multi-field rules, validate structure first (`array`, `distinct`, etc.).
- In public APIs, document what `UNKNOWN` means to consumers.

## Testing
- `Validator::make($data, ['state' => 'ternary_true'])->passes()`.
- Mock weighted rules via `ternary_weighted:weights=lead:5,auto:1,requiredRatio=0.7`.
- Cover DSL cases with `true`, `false`, `unknown`, and legacy strings.

## Observability
- `ternary_*` rules can attach `TernaryDecisionReport` to the validator (via `setData`) for audit purposes.
- Enable `config('trilean.validation.log_failures')` to capture invalid inputs.

> Ternary validation is the first defensive line, ensuring downstream flows operate with unambiguous states.
