# 🎨 Directivas Blade Ternarias (10+)

> Construye interfaces expresivas que comuniquen estados ternarios de forma consistente y sin boilerplate.

## Visión General
Se registran mediante `Blade::if` y `Blade::directive` en el `TernaryLogicServiceProvider`. Otorgan semántica ternaria a las vistas, reduciendo `@php` o `@if` complejos.

## Directivas Disponibles
| Directiva | Propósito |
| --- | --- |
| `@ternary($value)` | Renderiza cuando el estado es `TRUE` |
| `@ternaryTrue($value)` | Alias de `@ternary` |
| `@ternaryFalse($value)` | Renderiza cuando es `FALSE` |
| `@ternaryUnknown($value)` | Renderiza cuando es `UNKNOWN` |
| `@maybe($value)` | Pattern matching inline |
| `@ternaryMatch($value)` + `@case('true')` | Switch ternario |
| `@ternaryBadge($value, $labels = [])` | Badge consistente |
| `@ternaryIcon($value, ...)` | Mapeo rápido de íconos |
| `@allTrue(...)` / `@anyTrue(...)` | Gates multi-valor en vistas |
| `@ternaryTooltip($value)` | Tooltip contextual (opcional) |

## Ejemplos

```blade
@ternary($user->kyc_state)
    <span class="text-emerald-600">Documentación en regla</span>
@else
    <span class="text-amber-500">Verificación pendiente</span>
@endternary
```

```blade
@ternaryMatch($deployment->health_state)
    @case('true')
        <x-status-badge type="success">Estable</x-status-badge>
    @case('false')
        <x-status-badge type="danger">Inestable</x-status-badge>
    @case('unknown')
        <x-status-badge type="warning">Monitorizando</x-status-badge>
@endternaryMatch
```

```blade
@ternaryBadge($server->uptime_state, [
    'true' => 'En línea',
    'false' => 'Fuera de línea',
    'unknown' => 'Degradado',
])
```

```blade
@allTrue($user->permissions['reports'], $user->permissions['exports'])
    <x-report-button />
@endallTrue
```

## Personalización Visual
- **Config**: Publica `config/trilean.php` y ajusta `ui.badges` + `ui.icons`.
- **CSS**: Compatible con Tailwind, Bootstrap o clases propias vía configuración.
- **Accesibilidad**: `@ternaryBadge` incluye labels amigables usando `ternary_match`.

## Buenas Prácticas
- Pasa estados desde el controlador para mantener vistas limpias.
- Encapsula UI compleja en componentes (`<x-ternary-badge />`).
- Documenta directivas custom que agregues al proyecto.

## Testing
- `Blade::render()` para validar markup.
- `assertSee/ assertDontSee` en `TestResponse`.
- Asegura que `config('trilean.ui')` respete tu diseño.

## Observabilidad
- `@ternaryBadge` acepta `report` para agregar metadata en atributos `data-*`.
- Sincroniza Livewire/Vue con `ternary_match` para actualizaciones en tiempo real.

> Las directivas eliminan ruido en las vistas y estandarizan cómo se presenta cada estado ternario, reduciendo regresiones visuales.
