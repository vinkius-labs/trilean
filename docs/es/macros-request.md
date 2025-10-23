# 🌐 Macros Ternarias para Request (5 métodos)

> Lleva la expresividad ternaria directamente a `Illuminate\Http\Request`, reduciendo validaciones manuales y permitiendo gates en el borde de la aplicación.

## Visión General
Se registran en el `boot()` del service provider y están disponibles en cualquier request (HTTP, pruebas, comandos con `Request::create`). Apoyan en helpers globales y `TernaryExpressionEvaluator` para evaluar DSL personalizadas.

## Lista de Macros
| Macro | Firma | Retorno | Propósito |
| --- | --- | --- | --- |
| `ternary(string $key, mixed $default = null)` | `TernaryState` | Normalización inline |
| `hasTernaryTrue(string $key)` | `bool` | Verificar `TRUE` |
| `hasTernaryFalse(string $key)` | `bool` | Verificar `FALSE` |
| `hasTernaryUnknown(string $key)` | `bool` | Detectar `UNKNOWN` |
| `ternaryGate(array|string $keys, array $options = [])` | `TernaryState` o `TernaryDecisionReport` | Gate multi-campo |
| `ternaryExpression(string $expression, array $context = [])` | `TernaryState` | Evaluar DSL |

*( `ternaryExpression` se expone cuando el evaluador está configurado )*

## Ejemplos

### Normalización Rápida
```php
public function store(Request $request)
{
    $state = $request->ternary('eligibility');

    if ($state->isFalse()) {
        abort(403, 'Usuario inelegible');
    }
}
```

### Inspección de Flags
```php
if ($request->hasTernaryUnknown('kyc_status')) {
    Audit::logPending($request->user());
}
```

### Gate de Seguridad
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

### Expresiones Ternarias
```php
$result = $request->ternaryExpression('kyc && (aml || manual_override)');
```
La expresión se convierte en un AST y se evalúa con `TernaryExpressionEvaluator`, soportando `&&`, `||`, `!`, `xor`, paréntesis, literales `true/false/unknown` y aliases custom.

## Buenas Prácticas
- Usa `request()->ternary('key')` en middleware ternarios.
- Documenta qué operador se aplica en `ternaryGate` para facilitar mantenimiento.
- Combina con `FormRequest` + reglas `ternary_*` antes de consumir las macros.
- En tests, crea requests sintéticos: `Request::create('/', 'POST', ['flag' => 'unknown'])`.

## Observabilidad
- Configura `'report' => true` en `ternaryGate` para almacenar `TernaryDecisionReport` en logs.
- Usa `ternaryExpression` con contexto (`['threshold' => 0.6]`) para ajustar decisiones sin desplegar.

## Errores Comunes
- Olvidar defaults: si la clave no existe, `ternary()` devuelve `UNKNOWN` salvo que indiques `$default`.
- Strings personalizados sin alias: define aliases en el evaluador (`pending`, `approved`, etc.).

> Las macros de Request evitan reimplementaciones de normalización y mantienen decisiones coherentes entre frontend, API y servicios internos.
