# 🗄️ Eloquent Scopes Ternários (8 métodos)

> Extensões fluentes para trabalhar com campos ternários diretamente em consultas Eloquent e Builder.

## Introdução
Os scopes são registrados via `Builder::macro` e `EloquentBuilder::macro` no service provider. Eles funcionam tanto em consultas quanto em relacionamentos (`$query->with(...)`). São pensados para manter consistência entre filtros de API, dashboards e jobs de manutenção.

## Scopes Disponíveis
| Scope | Parâmetros | Resultado |
| --- | --- | --- |
| `whereTernaryTrue($column)` | string $column, ?callable $callback = null | Filtra registros `TRUE` |
| `whereTernaryFalse($column)` | idem | Filtra registros `FALSE` |
| `whereTernaryUnknown($column)` | idem | Filtra registros `UNKNOWN` |
| `orderByTernary($column, $direction = 'desc')` | string $direction | Ordena priorizando estados |
| `whereAllTernaryTrue(array $columns)` | array $columns | Todas as colunas `TRUE` |
| `whereAnyTernaryTrue(array $columns)` | array $columns | Pelo menos uma `TRUE` |
| `whereNoneTernaryFalse(array $columns)` | array $columns | Nenhuma `FALSE` |
| `ternaryConsensus(array $columns, array $options = [])` | arrays | Consenso ponderado |

## Funcionamento Interno
- Todos os scopes chamam `ternary()` para normalizar e geram cláusulas compatíveis com SQL padrão.
- Os campos são tratados como strings (`'true'`, `'false'`, `'unknown'`) ou inteiros (`1`, `0`, `-1`), dependendo da configuração de casting.
- Quando o modelo usa `TernaryCasts`, o scope reconhece automaticamente os valores salvos.

## Exemplos Práticos

### Filtro Básico
```php
Order::query()
    ->whereTernaryTrue('compliance_state')
    ->whereTernaryUnknown('fraud_state')
    ->get();
```

### Ordenação Inteligente
```php
$items = Inventory::query()
    ->orderByTernary('health_state') // TRUE > UNKNOWN > FALSE
    ->orderByDesc('updated_at')
    ->paginate();
```

### Combinação de Múltiplas Colunas
```php
Project::query()
    ->whereAllTernaryTrue(['legal_state', 'finance_state'])
    ->whereNoneTernaryFalse(['security_state', 'privacy_state'])
    ->get();
```

### Consenso Ponderado em SQL
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
Internamente, o scope utiliza subconsultas/CTEs leves (dependendo da versão do Laravel) para simular o consenso pela camada SQL. Quando não é possível, ele coleta IDs, aplica o consenso em PHP e remonta a consulta (`whereIn`).

## Boas Práticas
- **Casts**: Use `protected $casts = ['field' => TernaryState::class]` para reduzir conversões.
- **Índices**: Crie índices compostos quando usar `whereAll`/`whereAny` com frequência.
- **Lazy Loading**: Ao acessar relacionamentos, encadeie scopes para evitar N+1 (`$user->orders()->whereTernaryTrue('fraud_state')->count()`).
- **Auditoria**: Combine com `TernaryDecisionReport::capture($query, $state)` em repositórios para rastrear decisões.

## Testes
- Utilize `Model::factory()->create(['state' => TernaryState::true()->toDatabase()])`.
- Para assertar SQL gerado, use `toSql()` e snapshots.
- Ao testar consenso, verifique tanto o resultado `TernaryState` quanto a lista de IDs filtrados.

## Estratégias de Migração
- Ao migrar campos booleanos existentes, crie uma migration que converte `null` para `'unknown'` (ou `0`/`-1`).
- Atualize factories e seeders para incluir os três estados.
- Documente no modelo quais colunas são ternárias (útil para dicionários de dados).

> Os scopes ajudam a manter a lógica ternária próxima ao banco, reduzindo divergências entre camadas e fornecendo performance consistente.
