# ✅ Reglas de Validación Ternarias

> Asegura la integridad de entrada al aceptar y combinar estados ternarios en formularios, APIs y eventos.

## Visión General
Las reglas se registran con `Validator::extend` en el service provider, mapeando nombres amigables a closures que usan `TernaryLogicService` y helpers. Funcionan en `FormRequest`, validación manual (`Validator::make`) o reglas `Rule::` custom.

## Reglas Disponibles
| Regla | Tipo | Descripción |
| --- | --- | --- |
| `ternary` | Unitaria | Debe ser convertible a `TernaryState` |
| `ternary_true` | Unitaria | Debe resolver en `TRUE` |
| `ternary_not_false` | Unitaria | No puede ser `FALSE` |
| `ternary_gate` | Multi | Aplica operador (`and`, `or`, `xor`, `consensus`) |
| `ternary_any_true` | Multi | Algún campo `TRUE` |
| `ternary_all_true` | Multi | Todos los campos `TRUE` |
| `ternary_consensus` | Multi | Votación custom |
| `ternary_weighted` | Multi | Decisión ponderada |
| `ternary_expression` | Multi | Evalúa DSL ternaria |

## Uso Básico
```php
$request->validate([
    'kyc_state' => ['required', 'ternary'],
    'aml_state' => ['required', 'ternary_not_false'],
]);
```

## Reglas Multi-campo
```php
$request->validate([
    'checks' => ['required', 'array'],
    'checks.*' => ['ternary'],
    'checks' => ['ternary_gate:and'],
]);
```
- **Sintaxis**: `ternary_gate:operator,options...`
- **Opciones**: `requiredRatio=0.66`, `weights=legal:5,finance:3`, `report=true`.

### `ternary_expression`
```php
$request->validate([
    'decision' => ['required', 'ternary_expression:kyc && (aml || override)'],
]);
```
- Usa campos del request (`kyc`, `aml`, `override`).
- `null` -> `UNKNOWN`; fallos dependen del contexto.

## Mensajes Personalizados
En `lang/es/validation.php`:
```php
'ternary' => 'El campo :attribute debe ser verdadero, falso o desconocido.',
'ternary_gate' => 'La combinación ternaria de :attribute no alcanzó el umbral requerido.',
```

## Buenas Prácticas
- Aplica `ternary`/`ternary_not_false` antes de usar macros.
- En reglas multi-campo valida estructura (`array`, `distinct`).
- En APIs públicas documenta el significado de `UNKNOWN`.

## Tests
- `Validator::make($data, ['state' => 'ternary_true'])->passes()`.
- Simula pesos: `ternary_weighted:weights=lead:5,auto:1,requiredRatio=0.7`.
- Cubre casos con literales y strings custom.

## Observabilidad
- Las reglas `ternary_*` pueden adjuntar `TernaryDecisionReport` al validator para auditoría.
- Habilita `config('trilean.validation.log_failures')` para rastrear inputs inválidos.

> Las validaciones son la primera barrera contra estados ambiguos, asegurando que el resto de los flujos opere con datos confiables.
