# 🗄️ Ternary Eloquent Scopes (8 methods)

> Fluent extensions for working with ternary fields directly inside Eloquent queries and builders.

## Introduction
Scopes are registered via `Builder::macro` and `EloquentBuilder::macro` within the service provider. They work in base queries and relationships (`$query->with(...)`). The goal is to keep ternary logic close to the database layer for consistent filtering and performance.

## Available Scopes
| Scope | Parameters | Result |
| --- | --- | --- |
| `whereTernaryTrue($column)` | `string $column, ?callable $callback = null` | Records with state `TRUE` |
| `whereTernaryFalse($column)` | same | Records with state `FALSE` |
| `whereTernaryUnknown($column)` | same | Records with state `UNKNOWN` |
| `orderByTernary($column, $direction = 'desc')` | `string $direction` | Priority ordering (TRUE > UNKNOWN > FALSE) |
| `whereAllTernaryTrue(array $columns)` | `array $columns` | All columns must be `TRUE` |
| `whereAnyTernaryTrue(array $columns)` | `array $columns` | At least one column `TRUE` |
| `whereNoneTernaryFalse(array $columns)` | `array $columns` | No column `FALSE` |
| `ternaryConsensus(array $columns, array $options = [])` | arrays | Weighted consensus in SQL |

## How It Works
- Scopes normalize values with `ternary()` and translate results into SQL-friendly expressions.
- Supports string (`'true'`, `'false'`, `'unknown'`) or integer storage (1, 0, -1) depending on casts.
- When models use `TernaryCasts`, scopes automatically respect stored representation.

## Practical Examples

### Basic Filtering
```php
Order::query()
    ->whereTernaryTrue('compliance_state')
    ->whereTernaryUnknown('fraud_state')
    ->get();
```

### Smart Ordering
```php
$items = Inventory::query()
    ->orderByTernary('health_state') // TRUE > UNKNOWN > FALSE
    ->orderByDesc('updated_at')
    ->paginate();
```

### Multi-Column Combos
```php
Project::query()
    ->whereAllTernaryTrue(['legal_state', 'finance_state'])
    ->whereNoneTernaryFalse(['security_state', 'privacy_state'])
    ->get();
```

### Weighted Consensus in SQL
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
Behind the scenes the scope builds lightweight subqueries/CTEs (depending on Laravel version) or falls back to PHP post-processing when databases do not support the required functions.

## Best Practices
- **Casts**: Set `$casts = ['field' => TernaryState::class]` to minimize conversions.
- **Indexes**: Create compound indexes when using `whereAll`/`whereAny` frequently.
- **Lazy Loading**: Chain scopes on relationships to avoid N+1 queries.
- **Auditing**: Pair with `TernaryDecisionReport::capture($query, $state)` in repositories.

## Testing
- Use factories: `Model::factory()->create(['state' => TernaryState::true()->toDatabase()])`.
- Assert generated SQL via `toSql()` snapshots.
- For consensus scopes, assert both state and filtered IDs.

## Migration Strategy
- When migrating boolean columns, add migrations converting `null` to `'unknown'` (or 0 / -1).
- Update factories/seeders to include all three states.
- Document which columns behave ternarily for data catalogs.

> Scopes keep ternary reasoning in the data layer, reducing divergence across services and ensuring reliable performance.
