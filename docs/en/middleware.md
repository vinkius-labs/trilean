# 🛡️ Ternary Middleware

> Guard routes, queues, and pipelines by applying ternary logic before requests reach your core domain.

## Middleware
| Middleware | Description | Namespace |
| --- | --- | --- |
| `TernaryGateMiddleware` | Evaluates multiple attributes (user/request) using a configurable ternary operator | `Trilean\Http\Middleware` |
| `RequireTernaryTrue` | Blocks the request unless the specified attribute resolves to `TRUE` | `Trilean\Http\Middleware` |

## Setup
1. **Register in `Kernel`**
   ```php
   protected $routeMiddleware = [
       'ternary.gate' => \Trilean\Http\Middleware\TernaryGateMiddleware::class,
       'ternary.requireTrue' => \Trilean\Http\Middleware\RequireTernaryTrue::class,
   ];
   ```
2. **Attach to Routes**
   ```php
   Route::middleware('ternary.requireTrue:kyc_state')->group(function () {
       // protected routes
   });
   ```

## `TernaryGateMiddleware`
- **Default parameters**: `keys`, `source`, `operator`, `weights`, `requiredRatio`, `responseFactory`.
- **Flow**:
  1. Collect values from request or authenticated user.
  2. Normalize via `ternary()`.
  3. Apply operator (`and`, `or`, `xor`, `consensus`, `weighted`).
  4. Produce `TernaryDecisionReport` with explanations.
  5. Block with structured JSON (423/403) when result is `FALSE`.
- **Advanced use**:
  ```php
  Route::middleware('ternary.gate:checks,request,weighted,requiredRatio=0.66')
      ->post('/payouts', PayoutController::class);
  ```
  Expects `checks` payload (array/string of states).
- **Custom response**: Supply a `responseFactory` closure receiving `(Request $request, TernaryDecisionReport $report)`.

## `RequireTernaryTrue`
- **Simple usage**:
  ```php
  Route::middleware('ternary.requireTrue:user.compliance_state')
      ->post('/investments', ...);
  ```
- **Sources**: `user`, `request`, `route`, `payload` (default `user`).
- **Behavior**: Aborts with `403` or `Retry-After` if state is `FALSE` or `UNKNOWN` (configurable).

## Observability
- Both middleware log via `TrileanLogger` when enabled (`config('trilean.logging')`).
- `TernaryGateMiddleware` can fire `TernaryDecisionEvaluated` events for downstream integrations.
- Export to Prometheus/DataDog with `DecisionMetrics::record($report)`.

## Testing
- Use `actingAs` with users returning `TernaryState` attributes.
- For payload checks, call `$this->postJson('/endpoint', ['checks' => ['true', 'unknown']])`.
- Assert JSON fragments when reports are included in the response.

## Common Scenarios
- **Compliance**: Block financial operations until audits stop being `UNKNOWN`.
- **Feature Early Access**: Gate routes behind centralized flags resolved via ternary consensus.
- **Infrastructure**: Prevent deploy endpoints from running while CI signals `FALSE`.

> Middleware enforce business logic at the edge, ensuring inconsistent states are handled before they reach critical code paths.
