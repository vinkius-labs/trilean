# 🌐 Ternary Request Macros (5 methods)

> Bring ternary expressiveness directly onto `Illuminate\Http\Request`, reducing manual validation glue and enabling decision gates at the edge.

## Overview
Macros are registered in the provider's `boot()` method and available on every request (HTTP, console testing, jobs created via `Request::create`). They lean on global helpers and the `TernaryExpressionEvaluator` for custom DSL evaluation.

## Macro List
| Macro | Signature | Returns | Purpose |
| --- | --- | --- | --- |
| `ternary(string $key, mixed $default = null)` | `TernaryState` | Inline normalization |
| `hasTernaryTrue(string $key)` | `bool` | Flag check for `TRUE` |
| `hasTernaryFalse(string $key)` | `bool` | Flag check for `FALSE` |
| `hasTernaryUnknown(string $key)` | `bool` | Detect missing / pending decision |
| `ternaryGate(array|string $keys, array $options = [])` | `TernaryState` or `TernaryDecisionReport` | Multi-field gate |
| `ternaryExpression(string $expression, array $context = [])` | `TernaryState` | DSL evaluation |

*(`ternaryExpression` becomes available when the evaluator is configured.)*

## Examples

### Quick Normalization
```php
public function store(Request $request)
{
    $state = $request->ternary('eligibility');

    if ($state->isFalse()) {
        abort(403, 'User not eligible');
    }
}
```

### Flag Inspection
```php
if ($request->hasTernaryUnknown('kyc_status')) {
    Audit::logPending($request->user());
}
```

### Security Gate
```php
$decision = $request->ternaryGate([
    'document_verification',
    'aml_screening',
    'internal_whitelist',
], options: [
    'operator' => 'and',
    'requiredRatio' => 0.66,
    'report' => true,
]);

if ($decision->state->isFalse()) {
    return response()->json($decision->toArray(), 423);
}
```

### Ternary Expressions
```php
$result = $request->ternaryExpression('kyc && (aml || manual_override)');
```
Expressions are parsed into an AST and evaluated via `TernaryExpressionEvaluator`, supporting `&&`, `||`, `!`, `xor`, parentheses, literal `true/false/unknown`, and custom aliases.

## Best Practices
- Prefer `request()->ternary('key')` in middleware instead of raw `input()`.
- When using `ternaryGate`, document chosen operators (`and`, `consensus`, etc.) for maintainability.
- Pair with `FormRequest` validation (`ternary_*` rules) before using macros.
- In tests, create synthetic requests with `Request::create('/', 'POST', ['flag' => 'unknown'])`.

## Observability
- Pass `'report' => true` to `ternaryGate` to capture a `TernaryDecisionReport` for logging.
- Use `ternaryExpression` with contextual values (`['threshold' => 0.6]`) to adjust decisions without redeploying.

## Common Pitfalls
- Missing defaults: if the key is absent, `ternary()` returns `UNKNOWN` unless you supply a `$default`.
- Custom strings without alias mapping: configure the evaluator to accept legacy labels (`pending`, `approved`, etc.).

> Request macros ensure gateways and controllers avoid re-implementing normalization logic, keeping decisions consistent across front-end, APIs, and internal services.
