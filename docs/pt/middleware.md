# 🛡️ Middleware Ternário

> Proteja rotas, filas e pipelines aplicando lógica ternária antes de continuar o fluxo.

## Middleware Disponíveis
| Middleware | Descrição | Namespace |
| --- | --- | --- |
| `TernaryGateMiddleware` | Avalia múltiplos atributos (user/request) com operador ternário configurável | `Trilean\Http\Middleware` |
| `RequireTernaryTrue` | Bloqueia a requisição se o atributo especificado não for `TRUE` | `Trilean\Http\Middleware` |

## Configuração
1. **Registrar no Kernel**
   ```php
   protected $routeMiddleware = [
       'ternary.gate' => \Trilean\Http\Middleware\TernaryGateMiddleware::class,
       'ternary.requireTrue' => \Trilean\Http\Middleware\RequireTernaryTrue::class,
   ];
   ```
2. **Definir Rotas**
   ```php
   Route::middleware('ternary.requireTrue:kyc_state')->group(function () {
       // rotas protegidas
   });
   ```

## TernaryGateMiddleware
- **Parâmetros padrão**: `keys`, `source`, `operator`, `weights`, `requiredRatio`, `responseFactory`.
- **Funcionamento**:
  1. Coleta valores do `Request` ou do usuário autenticado.
  2. Normaliza via `ternary()`.
  3. Executa operador (`and`, `or`, `xor`, `consensus`, `weighted`).
  4. Gera `TernaryDecisionReport` (incluindo `explanations`).
  5. Bloqueia com resposta 423/403 se resultado `FALSE`.
- **Exemplo de uso avançado**:
  ```php
  Route::middleware('ternary.gate:checks,request,weighted,requiredRatio=0.66')
      ->post('/payouts', PayoutController::class);
  ```
  Onde a requisição deve conter `checks` (`array|string` de estados).
- **Custom Response**: Forneça `responseFactory` que aceite `(Request $request, TernaryDecisionReport $report)`.

## RequireTernaryTrue
- **Uso Simples**:
  ```php
  Route::middleware('ternary.requireTrue:user.compliance_state')
      ->post('/investments', ...);
  ```
- **Fonte**: `user`, `request`, `route`, `payload` (default `user`).
- **Comportamento**: Se o estado for `FALSE` ou `UNKNOWN`, aborta com `403` ou `Retry-After` se configurado.

## Observabilidade
- Ambos middleware registram logs via `TrileanLogger` quando habilitado (`config('trilean.logging')`).
- `TernaryGateMiddleware` pode disparar eventos `Trilean\Events\TernaryDecisionEvaluated` para integrações.
- Integre com Prometheus/DataDog via `DecisionMetrics::record($report)`.

## Testes
- Use `actingAs` com usuários cujos atributos retornem `TernaryState`.
- Para testar request payload, utilize `$this->postJson('/endpoint', ['checks' => ['true', 'unknown']])`.
- Valide resposta JSON contendo `report` (se habilitado) com `assertJsonFragment`.

## Casos de Uso Comuns
- **Compliance**: bloquear operações financeiras enquanto auditorias estiverem como `UNKNOWN`.
- **Feature Early-Access**: liberar rota apenas se gate retornou `TRUE` em flag centralizada.
- **Infraestrutura**: impedir deploy se verificação externa (`CI/CD`) sinalizou `FALSE`.

> Middleware ternários aplicam regras de negócio na borda, garantindo que estados inconsistentes sejam tratados antes de atingir o core da aplicação.
