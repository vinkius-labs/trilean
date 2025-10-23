# 🌐 Request Macros Ternários (5 métodos)

> Traga a expressividade ternária direto para `Illuminate\Http\Request`, reduzindo validações manuais e facilitando gateways de decisão.

## Visão Geral
As macros são registradas no `boot()` do service provider e ficam acessíveis em qualquer request (HTTP, console testing, jobs com `Request::create`). Elas dependem dos helpers globais e do `TernaryExpressionEvaluator` para lidar com DSLs personalizadas.

## Macros Disponíveis
| Macro | Assinatura | Retorno | Uso Principal |
| --- | --- | --- | --- |
| `ternary(string $key, mixed $default = null)` | retorna `TernaryState` | Normalização inline |
| `hasTernaryTrue(string $key)` | retorna `bool` | Flags positivas |
| `hasTernaryFalse(string $key)` | retorna `bool` | Flags negativas |
| `hasTernaryUnknown(string $key)` | retorna `bool` | Detectar ausência de decisão |
| `ternaryGate(array|string $keys, array $options = [])` | retorna `TernaryState` ou `TernaryDecisionReport` | Por ta gate multi-campos |
| `ternaryExpression(string $expression, array $context = [])` | retorna `TernaryState` | DSL declarativa |

*(São 5 macros principais; `ternaryExpression` é exposta quando o avaliador está habilitado.)*

## Exemplos

### Normalização Rápida
```php
public function store(Request $request)
{
    $state = $request->ternary('eligibility');

    if ($state->isFalse()) {
        abort(403, 'Usuário inelegível');
    }
}
```

### Inspeção de Flags
```php
if ($request->hasTernaryUnknown('kyc_status')) {
    Audit::logPending($request->user());
}
```

### Porta de Segurança
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

### Expressões Ternárias
```php
$result = $request->ternaryExpression('kyc && (aml || manual_override)');
```
Internamente, a expressão é convertida para um AST e avaliada usando `TernaryExpressionEvaluator`, aceitando operadores `&&`, `||`, `??`, parênteses, `!`, `xor`, valores `true/false/unknown` e aliases customizados.

## Boas Práticas
- Prefira `request()->ternary('key')` em vez de acessar `input()` direto em middlewares ternários.
- Quando usar `ternaryGate`, documente no README qual operador está sendo aplicado para facilitar manutenção.
- Combine com `FormRequest` validando via regras `ternary_*` antes de consumir as macros.
- Para testes, use `Request::create('/', 'POST', ['flag' => 'unknown'])` e assert por macro.

## Observabilidade
- Habilite `report => true` em `ternaryGate` para armazenar `TernaryDecisionReport` e anexar a logs.
- Use `ternaryExpression` com `context` (`['threshold' => 0.6]`) para ajustar decisões sem redeploy.

## Erros Comuns
- Esquecer de definir valores padrão: se a chave não existir, `ternary()` usa `$default` (`null` vira `UNKNOWN`).
- Misturar strings customizadas sem mapeamento: configure aliases no evaluador para aceitar rótulos legados (`pending`, `approved`).

> As macros de Request permitem que gateways e controllers evitem repetir lógica de normalização, mantendo decisões consistentes entre front-end, API e serviços internos.
