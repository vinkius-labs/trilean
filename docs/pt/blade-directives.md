# 🎨 Diretivas Blade Ternárias (10+)

> Crie interfaces expressivas que comunicam estados ternários de forma consistente e reutilizável.

## Visão Geral
As diretivas são registradas via `Blade::if` e `Blade::directive` no `TernaryLogicServiceProvider`. Elas conferem semântica ternária para views, reduzindo `@php` ou `@if` complexos.

## Diretivas Disponíveis
| Diretiva | Propósito |
| --- | --- |
| `@ternary($value)` | Renderiza block apenas quando `TRUE` |
| `@ternaryTrue($value)` | Alias para `@ternary` |
| `@ternaryFalse($value)` | Renderiza block quando `FALSE` |
| `@ternaryUnknown($value)` | Renderiza block quando `UNKNOWN` |
| `@maybe($value)` | Executa pattern matching inline |
| `@ternaryMatch($value)` + `@case('true')` | Switch ternário |
| `@ternaryEndcase` / `@endternaryMatch` | Fechamentos |
| `@ternaryBadge($value, $labels = [])` | Badge bootstrap/tailwind |
| `@ternaryIcon($value, $icons = [])` | Ícones automaticamente mapeados |
| `@allTrue(...)` / `@anyTrue(...)` | Portas lógicas com múltiplos argumentos |
| `@ternaryTooltip($value)` | Tooltip contextual (opcional) |

## Exemplos

### Condicionais Claras
```blade
@ternary($user->kyc_state)
    <span class="text-emerald-600">Documentação em dia</span>
@else
    <span class="text-amber-500">Verificação pendente</span>
@endternary
```

### Pattern Matching
```blade
@ternaryMatch($deployment->health_state)
    @case('true')
        <x-status-badge type="success">Estável</x-status-badge>
    @case('false')
        <x-status-badge type="danger">Instável</x-status-badge>
    @case('unknown')
        <x-status-badge type="warning">Monitorando</x-status-badge>
@ternaryEndcase
@endternaryMatch
```

### Badges Automáticos
```blade
@ternaryBadge($server->uptime_state, [
    'true' => 'Online',
    'false' => 'Offline',
    'unknown' => 'Degradado',
])
```
Gera markup com classes CSS configuráveis (`config('trilean.theme')`).

### Gating de Componentes
```blade
@allTrue($user->permissions['reports'], $user->permissions['exports'])
    <x-report-button />
@endallTrue
```

## Customização Visual
- **Configuração**: Publicar `config/trilean.php` e editar a seção `ui.badges` e `ui.icons`.
- **Stack CSS**: Funciona com Tailwind, Bootstrap ou custom via `config`.
- **Acessibilidade**: Diretivas `Badge/Icon` incluem atributo `aria-label` automático (`ternary_match`).

## Boas Práticas
- Evite lógica pesada dentro das views; passe estados do controller (`compact('state')`).
- Prefira componentes Blade (`<x-ternary-badge :state="$state" />`) quando precisar de markup avançado; as diretivas podem envolver esses componentes.
- Documente tokens adicionais (`@ternaryTooltip`) usados no projeto para onboarding rápido.

## Testes
- Use `Blade::render()` em testes para validar markup.
- Combine com `assertSee`/`assertDontSee` do `TestResponse`.
- Garanta que `config('trilean.theme')` está alinhado com guidelines de UI.

## Observabilidade
- `@ternaryBadge` aceita parâmetro `:report="$decisionReport"` para incluir dados de auditoria como data attributes.
- Instrumente componentes com Livewire/Vue usando `ternary_match` para sincronizar estado visual com websockets.

> As diretivas removem ruído de condicionais e padronizam a visualização de estados ternários, reduzindo bugs visuais e divergências entre times de produto.
