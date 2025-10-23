# 💎 Macros de Collection Trilean (12 métodos)

> Amplía `Illuminate\Support\Collection` con operaciones ternarias declarativas que mantienen los pipelines legibles, performantes y auditables.

## Visión General
Las macros se registran en el `TernaryLogicServiceProvider` durante el boot y quedan disponibles en cualquier `Collection` (y `LazyCollection` cuando aplica). Se apoyan en los helpers globales y `TernaryLogicService`, garantizando coherencia en controladores, jobs y pipelines de datos.

## Tabla de Referencia
| Macro | Retorno | Propósito |
| --- | --- | --- |
| `ternaryConsensus()` | `TernaryState` | Unificar votos/estados |
| `ternaryMajority()` | `TernaryState` | Mayoría simple |
| `whereTernaryTrue()` | `Collection` | Filtrar elementos `TRUE` |
| `whereTernaryFalse()` | `Collection` | Filtrar elementos `FALSE` |
| `whereTernaryUnknown()` | `Collection` | Filtrar elementos `UNKNOWN` |
| `ternaryWeighted(array $weights)` | `TernaryState` | Decisión ponderada |
| `ternaryMap(callable $callback)` | `TernaryVector` | Transformaciones normalizadas |
| `ternaryScore()` | `int` | Métrica balanceada (+1/0/-1) |
| `allTernaryTrue()` | `bool` | Puerta AND |
| `anyTernaryTrue()` | `bool` | Puerta OR |
| `partitionTernary()` | `array<Collection>` | Divide en tres subconjuntos |
| `ternaryGate(array $options)` | `TernaryState` | AND/OR/XOR/consensus/weighted |

## Detalle de Macros

### `ternaryConsensus()`
- Normaliza cada item con `ternary()` y usa `TernaryLogicService::consensus`.
- Ideal para boards de aprobación o health checks multinodo.

### `ternaryMajority()`
- Mayoría simple sin pesos; empate -> `UNKNOWN`.
- Recomendado en clusters o failover distribuidos.

### `whereTernary*()`
- Combina `data_get` + `ternary()` para arrays u objetos.
- Firma: `whereTernaryTrue(string $key)` (análogos para `false`/`unknown`).

### `ternaryWeighted()`
- Decisiones con pesos; acepta arreglos asociativos.
- Falta de pesos -> valor 1.
- Con `['report' => true]` retorna `TernaryDecisionReport`.

### `ternaryMap()`
- Similar a `map`, pero obliga normalización y devuelve `TernaryVector`.
- Permite encadenar `sum`, `weighted`, `encode` sin salir del contexto ternario.

### `ternaryScore()`
- Traduce estados a +1/0/-1 y suma.
- Útil para dashboards, scoring y thresholds.

### `allTernaryTrue()` / `anyTernaryTrue()`
- Operan sobre toda la colección; aceptan callback opcional (`fn ($item) => $item->state`).

### `partitionTernary()`
- Retorna array con claves `'true'`, `'false'`, `'unknown'`, cada una una Collection.
- Ideal para paneles, exportaciones o reprocesos incrementales.

### `ternaryGate()`
- Aplica operador configurable (`and`, `or`, `xor`, `consensus`, `weighted`).
- Puede recibir closure con `TernaryVector` para lógica custom.

## Patrones Recomendados
- **Pipelines**: `map -> ternaryMap -> ternaryGate` mantiene el flujo declarativo.
- **Aggregates DDD**: expone métodos que devuelven `Collection` + macros para componer decisiones.
- **Jobs**: serializa instantáneas con `ternaryMap()->encoded()` para replays.

## Buenas Prácticas
- Normaliza datos externos antes de aplicar macros.
- Documenta las claves usadas en `whereTernary*`.
- Evita mezclar múltiples operadores en un mismo `ternaryGate`; extrae helpers.

## Testing
- Usa `collect([...])` con estados explícitos (`TernaryState::true()` etc.).
- Para macros basadas en claves, trabaja con DTOs o arrays.
- Usa `tap` + snapshots al probar `ternaryMap`.

## Observabilidad
- Prepara logs agregando `map(fn ($item) => [$item->id, ternary($item->state)])`.
- Exporta `ternaryMap()->toBits()` para analizar regresiones en pruebas de carga.

> Las macros convierten collections en una DSL ternaria, preservando claridad incluso con grandes volúmenes de reglas y estados.
