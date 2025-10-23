# 🗄️ Scopes Ternarios para Eloquent (8 métodos)

> Extensiones fluidas para trabajar con campos ternarios directamente en consultas Eloquent y Builders.

## Introducción
Los scopes se registran vía `Builder::macro` y `EloquentBuilder::macro` en el service provider. Funcionan en consultas base y relaciones (`$query->with(...)`). Mantienen la lógica ternaria cercana a la capa de datos para filtros consistentes y buen rendimiento.

## Scopes Disponibles
| Scope | Parámetros | Resultado |
| --- | --- | --- |
| `whereTernaryTrue($column)` | `string $column, ?callable $callback = null` | Registros con estado `TRUE` |
| `whereTernaryFalse($column)` | igual | Registros con estado `FALSE` |
| `whereTernaryUnknown($column)` | igual | Registros con estado `UNKNOWN` |
| `orderByTernary($column, $direction = 'desc')` | `string $direction` | Orden priorizando estados |
| `whereAllTernaryTrue(array $columns)` | `array $columns` | Todas las columnas `TRUE` |
| `whereAnyTernaryTrue(array $columns)` | `array $columns` | Al menos una `TRUE` |
| `whereNoneTernaryFalse(array $columns)` | `array $columns` | Ninguna `FALSE` |
| `ternaryConsensus(array $columns, array $options = [])` | arrays | Consenso ponderado |

## Funcionamiento
- Normaliza con `ternary()` y traduce a expresiones SQL portables.
- Soporta almacenamiento como string (`'true'`, `'false'`, `'unknown'`) o entero (1, 0, -1).
- Con `TernaryCasts` en el modelo, se adapta automáticamente.

## Ejemplos

```php
Order::query()
    ->whereTernaryTrue('compliance_state')
    ->whereTernaryUnknown('fraud_state')
    ->get();
```

```php
$items = Inventory::query()
    ->orderByTernary('health_state')
    ->orderByDesc('updated_at')
    ->paginate();
```

```php
Project::query()
    ->whereAllTernaryTrue(['legal_state', 'finance_state'])
    ->whereNoneTernaryFalse(['security_state', 'privacy_state'])
    ->get();
```

```php
$reports = Report::query()
    ->ternaryConsensus([
        'legal_state' => 5,
        'ops_state' => 3,
        'finance_state' => 2,
    ], options: [
        'requiredRatio' => 0.7,
        'includeUnknown' => true,
    ])
    ->get();
```

## Buenas Prácticas
- **Casts**: Define `$casts = ['campo' => TernaryState::class]`.
- **Índices**: Usa índices compuestos para `whereAll`/`whereAny` frecuentes.
- **Lazy Loading**: Encadena scopes en relaciones para evitar N+1.
- **Auditoría**: Combina con `TernaryDecisionReport::capture` en repositorios.

## Testing
- Factories: `Model::factory()->create(['state' => TernaryState::true()->toDatabase()])`.
- Usa `toSql()` + snapshots para validar consultas.
- En consenso, verifica estado e IDs filtrados.

## Migración
- Convierte booleanos existentes, traduciendo `null` a `'unknown'` (o 0 / -1).
- Actualiza factories/seeders con los tres estados.
- Documenta qué columnas son ternarias en tu glosario de datos.

> Estos scopes mantienen la lógica ternaria en la base de datos, reduciendo divergencias entre capas y ofreciendo rendimiento consistente.
