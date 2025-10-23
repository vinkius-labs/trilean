# 📘 Guía Trilean en Español

## Visión General
Trilean lleva la computación ternaria al ecosistema Laravel. Cada decisión reconoce `TRUE`, `FALSE` y `UNKNOWN`, evitando bugs causados por nulos y estados intermedios.

## Antes y Después
### Escenario: Activar un módulo premium
**Antes (booleans)**
```php
if ($user->verified && $user->consent && !$user->blocked) {
    return 'habilitado';
}

return 'denegado';
```

**Después (Trilean)**
```php
if (all_true($user->verified, $user->consent, !$user->blocked)) {
    return 'habilitado';
}

return ternary_match(false, [
    'true' => 'habilitado',
    'false' => 'denegado',
    'unknown' => 'revisar',
]);
```
El estado UNKNOWN deja de ser un agujero lógico.

### Escenario: Flujo de aprobaciones
**Antes**
```php
if (!$doc->legal_approved) {
    return 'legal pendiente';
}

if (!$doc->finance_approved) {
    return 'finanzas pendiente';
}

return 'publicado';
```

**Después**
```php
$estado = collect([
    $doc->legal_approved,
    $doc->finance_approved,
    $doc->manager_approved,
])->ternaryWeighted([5, 3, 2]);

return ternary_match($estado, [
    'true' => 'publicado',
    'false' => 'rechazado',
    'unknown' => 'en revisión',
]);
```
Se manejan pesos y se preserva el estado intermedio.

## Recursos Técnicos
### 1. 🔥 Helpers Globales (10 funciones)
- `ternary()` – Normaliza valores mediante `TernaryState::fromMixed`.
- `maybe()` – Implementa branching ternario con callbacks lazy.
- `trilean()` – Resuelve el servicio `TernaryLogicService` desde el contenedor.
- `ternary_vector()` – Envuelve colecciones en `TernaryVector` para operaciones matemáticas.
- `all_true()` – Aplica `TernaryLogicService::and` y retorna bool.
- `any_true()` – Evalúa puertas OR con `TernaryLogicService::or`.
- `none_false()` – Garantiza ausencia de `FALSE` combinando `or()` y `and()`.
- `when_ternary()` – Ejecuta closures según el estado.
- `consensus()` – Usa `TernaryLogicService::consensus` para acuerdos.
- `ternary_match()` – Pattern matching amigable para respuestas.

### 2. 💎 Macros de Collection (12 métodos)
- `ternaryConsensus()` / `ternaryMajority()` – Derivados de `TernaryVector`.
- `whereTernaryTrue/False/Unknown()` – Filtros usando `ternary()`.
- `ternaryWeighted()` – Pontea directamente a `trilean()->weighted`.
- `ternaryMap()` – Devuelve `TernaryVector` listo para nuevas operaciones.
- `ternaryScore()` – Suma balanceada (+1, 0, -1).
- `allTernaryTrue()` / `anyTernaryTrue()` – Atajos para puertas lógicas.
- `partitionTernary()` – Divide la colección en tres grupos.
- `ternaryGate()` – Permite AND/OR/XOR/consensus desde la colección.

### 3. 🗄️ Scopes de Eloquent (8 métodos)
- `whereTernaryTrue/False/Unknown()` – Traducen estados a SQL estándar.
- `orderByTernary()` – Ordena con `CASE` priorizando `TRUE`.
- `whereAllTernaryTrue()` / `whereAnyTernaryTrue()` – Reúnen múltiples columnas.
- `ternaryConsensus()` – Evalúa decisiones sobre registros ya cargados.

### 4. 🌐 Macros de Request (5 métodos)
- `ternary()` – Normaliza inputs del request.
- `hasTernaryTrue/False/Unknown()` – Validaciones express.
- `ternaryGate()` – Evalúa varias llaves con AND/OR/consensus.
- `ternaryExpression()` – Soporta la DSL ternaria directamente.

### 5. 🎨 Directivas Blade (10+)
- `@ternary`, `@ternaryTrue/False/Unknown` – Condicionales explícitas.
- `@maybe` – Rendering inline.
- `@ternaryMatch` + `@case` – Pattern matching.
- `@ternaryBadge` / `@ternaryIcon` – UI visual consistente.
- `@allTrue` / `@anyTrue` – Gating de múltiplos checks en la vista.

### 6. 🛡️ Middleware
- `TernaryGateMiddleware` – Controla acceso evaluando atributos de usuario y request.
- `RequireTernaryTrue` – Requiere un atributo en estado `TRUE` antes de continuar.

### 7. ✅ Rules de Validación
- Unitarias: `ternary`, `ternary_true`, `ternary_not_false`.
- Grupales: `ternary_gate`, `ternary_any_true`, `ternary_all_true`, `ternary_consensus`.
- Avanzadas: `ternary_weighted`, `ternary_expression`.

### 8. 🧮 Funcionalidades Avanzadas
- `TernaryArithmetic` – Operaciones balanceadas con carries.
- `CircuitBuilder` – Construye circuitos lógicos fluentes y exportables.
- Conversor BalancedTrit con soporte unicode y aliases extendidos.

## Casos de Uso Detallados
1. **Feature Flags** – Ahora con `maybe()` e `ternaryWeighted()` se obtiene fallback seguro.
2. **Workflows** – Estados UNKNOWN permiten monitorear progreso sin bloquear usuarios.
3. **Health Checks** – `ternaryMajority()` identifica degradación vs caída total.

## Ideas Futuras para Enamorar al Equipo
- **Cache ternario** con claves por estado.
- **Monitor ternario** en tiempo real (UI + webhooks).
- **Policies automáticas** integradas com `Gate` y fallback UNKNOWN.
- **Replay de decisiones** usando `encodedVector`.
- **Inspector Artisan** para diagnosticar rotas e inputs.

---
Con estas herramientas, tu equipo gana velocidad, expresividad y rastreabilidad en cada decisión crítica dentro de Laravel.
