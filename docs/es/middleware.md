# 🛡️ Middleware Ternario

> Protege rutas, colas y pipelines aplicando lógica ternaria antes de llegar al dominio principal.

## Middleware
| Middleware | Descripción | Namespace |
| --- | --- | --- |
| `TernaryGateMiddleware` | Evalúa múltiples atributos (usuario/request) con operador ternario configurable | `Trilean\Http\Middleware` |
| `RequireTernaryTrue` | Bloquea la request si el atributo no es `TRUE` | `Trilean\Http\Middleware` |

## Configuración
1. **Registrar en el Kernel**
   ```php
   protected $routeMiddleware = [
       'ternary.gate' => \Trilean\Http\Middleware\TernaryGateMiddleware::class,
       'ternary.requireTrue' => \Trilean\Http\Middleware\RequireTernaryTrue::class,
   ];
   ```
2. **Aplicar a Rutas**
   ```php
   Route::middleware('ternary.requireTrue:kyc_state')->group(function () {
       // rutas protegidas
   });
   ```

## `TernaryGateMiddleware`
- **Parámetros**: `keys`, `source`, `operator`, `weights`, `requiredRatio`, `responseFactory`.
- **Flujo**: recoge valores (request/usuario), normaliza, aplica operador (`and`, `or`, `xor`, `consensus`, `weighted`), genera `TernaryDecisionReport` y bloquea si el resultado es `FALSE`.
- **Uso avanzado**:
  ```php
  Route::middleware('ternary.gate:checks,request,weighted,requiredRatio=0.66')
      ->post('/payouts', PayoutController::class);
  ```
- **Respuesta custom**: provee `responseFactory` con `(Request $request, TernaryDecisionReport $report)`.

## `RequireTernaryTrue`
- **Ejemplo**:
  ```php
  Route::middleware('ternary.requireTrue:user.compliance_state')
      ->post('/investments', ...);
  ```
- **Fuentes**: `user`, `request`, `route`, `payload` (por defecto `user`).
- **Comportamiento**: aborta con `403` o `Retry-After` para `FALSE`/`UNKNOWN` (según config).

## Observabilidad
- Ambos Middleware pueden loguear vía `TrileanLogger` (`config('trilean.logging')`).
- `TernaryGateMiddleware` emite `TernaryDecisionEvaluated` para integraciones.
- Exporta métricas a Prometheus/DataDog con `DecisionMetrics::record($report)`.

## Tests
- `actingAs` con usuarios que devuelvan atributos ternarios.
- Para payloads: `$this->postJson('/endpoint', ['checks' => ['true', 'unknown']])`.
- Usa `assertJsonFragment` cuando incluyas reportes en la respuesta.

## Casos Comunes
- **Compliance**: bloquear operaciones financieras mientras haya `UNKNOWN`.
- **Feature Early Access**: proteger rutas detrás de flags centralizados.
- **Infraestructura**: impedir deploy si CI marca `FALSE`.

> Los middleware aplican reglas clave en la frontera, evitando que estados inconsistentes lleguen a las capas críticas.
