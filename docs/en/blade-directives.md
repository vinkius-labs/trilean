# 🎨 Ternary Blade Directives (10+)

> Build expressive interfaces that communicate ternary states consistently and without repetitive boilerplate.

## Overview
Directives are registered through `Blade::if` and `Blade::directive` inside the `TernaryLogicServiceProvider`. They provide ternary semantics to views, reducing the need for `@php` blocks or tangled `@if` trees.

## Available Directives
| Directive | Purpose |
| --- | --- |
| `@ternary($value)` | Render block when state is `TRUE` |
| `@ternaryTrue($value)` | Alias for `@ternary` |
| `@ternaryFalse($value)` | Render block when state is `FALSE` |
| `@ternaryUnknown($value)` | Render block when state is `UNKNOWN` |
| `@maybe($value)` | Inline pattern matching |
| `@ternaryMatch($value)` + `@case('true')` | Ternary switch |
| `@ternaryBadge($value, $labels = [])` | Consistent badge markup |
| `@ternaryIcon($value, ...)` | Quick icon mapping |
| `@allTrue(...)` / `@anyTrue(...)` | Multi-value gates inside templates |
| `@ternaryTooltip($value)` | Inject contextual tooltip (optional directive) |

## Examples

### Clear Conditionals
```blade
@ternary($user->kyc_state)
    <span class="text-emerald-600">Documentation verified</span>
@else
    <span class="text-amber-500">Verification pending</span>
@endternary
```

### Pattern Matching
```blade
@ternaryMatch($deployment->health_state)
    @case('true')
        <x-status-badge type="success">Stable</x-status-badge>
    @case('false')
        <x-status-badge type="danger">Unstable</x-status-badge>
    @case('unknown')
        <x-status-badge type="warning">Monitoring</x-status-badge>
@endternaryMatch
```

### Automatic Badges
```blade
@ternaryBadge($server->uptime_state, [
    'true' => 'Online',
    'false' => 'Offline',
    'unknown' => 'Degraded',
])
```
Uses package configuration (`config('trilean.ui')`) to inject classes/icons.

### Component Gating
```blade
@allTrue($user->permissions['reports'], $user->permissions['exports'])
    <x-report-button />
@endallTrue
```

## Visual Customization
- **Configuration**: Publish `config/trilean.php` and adjust `ui.badges` + `ui.icons`.
- **CSS Stack**: Works with Tailwind, Bootstrap, or your own classes via config.
- **Accessibility**: Badge directives include `aria-label` friendly defaults via `ternary_match`.

## Best Practices
- Pass state from controller (`return view('...', ['state' => $state])`) to keep templates lean.
- Wrap complex UI in dedicated components (`<x-ternary-badge :state="$state" />`); directives can render those components.
- Document any custom directives (`@ternaryTooltip`) introduced in your project for new teammates.

## Testing
- Use `Blade::render()` to assert markup in feature tests.
- Combine with `assertSee` / `assertDontSee` on `TestResponse` instances.
- Ensure `config('trilean.theme')` (or equivalent) matches your design system.

## Observability
- `@ternaryBadge` supports a `report` parameter to embed decision metadata into `data-*` attributes.
- When using Livewire or front-end frameworks, rely on `ternary_match` to sync with real-time updates.

> Directives remove noise from your views, standardize how ternary states are presented, and reduce visual regressions across teams.
