# 🔥 Helpers Globales de Trilean

> Referencia completa de las funciones helper registradas por el paquete para acelerar flujos ternarios en Laravel.

## Visión General
Los helpers globales exponen operaciones frecuentes de `TernaryLogicService` mediante funciones idiomáticas en PHP. Simplifican condicionales, normalizaciones y decisiones complejas manteniendo el código expresivo y testeable. Están disponibles tras registrar `TernaryLogicServiceProvider`.

## Tabla Rápida
| Helper | Firma | Retorno | Uso Principal |
| --- | --- | --- | --- |
| `ternary()` | `mixed $value, ?string $field = null` | `TernaryState` | Normalización consistente |
| `maybe()` | `mixed $value, array $callbacks = []` | `mixed` | Control de flujo sin `if`|
| `trilean()` | `void` | `TernaryLogicService` | Resolver el servicio core |
| `ternary_vector()` | `iterable $values` | `TernaryVector` | Operaciones matemáticas |
| `all_true()` | `mixed ...$values` | `bool` | Puertas AND |
| `any_true()` | `mixed ...$values` | `bool` | Puertas OR |
| `none_false()` | `mixed ...$values` | `bool` | Garantizar ausencia de `FALSE` |
| `when_ternary()` | `mixed $value, array $callbacks` | `mixed` | Side effects lazy por estado |
| `consensus()` | `iterable $values, array $options = []` | `TernaryState` | Votaciones y quórums |
| `ternary_match()` | `mixed $value, array $map, mixed $default = null` | `mixed` | Pattern matching legible |

## Detalle por Helper

### `ternary()`
- **Objetivo**: Convertir cualquier valor en `TernaryState` (`true`, `false`, `unknown`).
- **Implementación**: `TernaryState::fromMixed` con heurísticas para booleanos, enteros, strings, `null`, enums y atributos Eloquent.
- **Casos de uso**: normalizar inputs de formularios, pipelines de Collection, logs homogéneos.
- **Ejemplo**:
  ```php
  $state = ternary($request->input('risk_level'));

  if ($state->isUnknown()) {
      return response()->json(['status' => 'awaiting-data']);
  }
  ```
- **Buenas prácticas**: informar `$field` para mensajes amigables; combinar con `data_get` en estructuras anidadas; documentar contratos esperados.

### `maybe()`
- **Objetivo**: Ramificación ternaria declarativa sin `if/else` repetidos.
- **Firma**: `function maybe(mixed $value, array $callbacks = [], mixed $fallback = null)`
- **Callbacks**: `'true'`, `'false'`, `'unknown'` (obligatorios) + `'any'` (post-procesamiento) y `'default'`.
- **Ejemplo**:
  ```php
  return maybe($featureFlag, [
      'true' => fn () => $this->enablePremium(),
      'false' => fn () => $this->logSkip('flag disabled'),
      'unknown' => fn () => $this->queueReview(),
      'any' => fn ($state) => Metrics::record('flags.checked', $state->name),
  ]);
  ```
- **Notas**: callbacks son lazy; mantén retornos consistentes; ideal junto a reportes de decisión.

### `trilean()`
- Resolver el servicio principal sin boilerplate del contenedor.
- Ubicación: `function trilean(): TernaryLogicService`.
- Útil para operaciones avanzadas (`xor`, `weighted`, `consensus`) y mocking en tests.

### `ternary_vector()`
- **Objetivo**: Encapsular colecciones ternarias con APIs matemáticas (`sum`, `majority`, `weighted`, `encode`).
- **Ejemplo**:
  ```php
  $vector = ternary_vector([$sensorA, $sensorB, $sensorC]);

  if ($vector->majority()->isTrue()) {
      dispatch(new ActivateFailover);
  }
  ```
- **Advertencia**: normaliza datos heterogéneos antes de usarlos; usa pesos asociativos para claridad.

### `all_true()`
- Puerta AND ternaria; retorna `false` si existe `FALSE` o `UNKNOWN`.
- Ejemplo: validar onboarding antes de liberar un feature.

### `any_true()`
- Puerta OR ternaria; retorna `true` con un solo `TRUE`, `false` si todo es `UNKNOWN`.

### `none_false()`
- Garantiza que nadie vetó la decisión:
  ```php
  if (none_false($policy->legal, $policy->compliance, $policy->security)) {
      Approvals::record($policy);
  }
  ```

### `when_ternary()`
- Centraliza side effects por estado con fallback.
- Ideal en observers, listeners y middleware.

### `consensus()`
- Calcula votaciones con opciones como `requiredRatio`, `weights`, `tieBreakers`.
- Ejemplo con pesos diferenciados por área.

### `ternary_match()`
- Mapear estados a salidas humanas (labels, respuestas, UI).
- Acepta closures y callback `'any'` para post-procesamiento.

## Estrategias Combinadas
- **Feature flags**: `when_ternary()` + `any_true()` + `ternary_match()`.
- **APIs BFF**: normalizar con `ternary()`, agregar con `consensus()` antes de responder.
- **Logs/Métricas**: usar `TernaryDecisionReport` para formatos estándar.

## Tests y Debug
- Mockear servicio con `app()->instance(TernaryLogicService::class, $fake)`.
- Usar `TernaryState::true()/false()/unknown()` en asserts claros.
- Capturar eventos/ reportes para validar side effects.

## Checklist de Adopción
- [ ] ¿Reemplazaste condicionales frágiles con helpers?
- [ ] ¿Documentaste contratos esperados para inputs ternarios?
- [ ] ¿Instrumentaste decisiones con métricas/logs?
- [ ] ¿Agregaste tests para caminos `UNKNOWN`?

> Usa los helpers como capa base; reducen divergencias y aceleran el resto del ecosistema Trilean.
