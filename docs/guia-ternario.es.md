# 📘 Guía Trilean en Español

## 🎯 Visión General
Trilean trae **computación ternaria** a Laravel. Cada decisión abraza `TRUE`, `FALSE` y `UNKNOWN`, eliminando sorpresas causadas por incompatibilidades de valores nulos/fuente de verdad.

**¿Por qué Trilean?**
- 🔒 **Type-safe** lógica de tres estados (no más bugs de `null`)
- 🚀 **Cero boilerplate** con helpers globales y macros
- 🎨 **Expresivo** directivas Blade y reglas de validación
- 📊 **Observabilidad** con métricas y seguimiento de decisiones integrados
- 🧮 **Avanzado** consenso, votación ponderada y aritmética balanceada

---

## 🔄 Antes vs Después

### Escenario 1: Onboarding de Usuario
**❌ Antes (caos booleano)**
```php
// Bugs ocultos: ¿y si verified es NULL?
if ($user->verified && $user->email_confirmed && $user->terms_accepted) {
    $user->activate();
    return redirect('/dashboard');
}

// Sin visibilidad del POR QUÉ no pueden continuar
return back()->with('error', 'No se puede activar la cuenta');
```

**✅ Después (Trilean)**
```php
// Directo y obvio
if (and_all($user->verified, $user->email_confirmed, $user->terms_accepted)) {
    $user->activate();
    return redirect('/dashboard');
}

// Manejo claro de cada estado
$decision = vote($user->verified, $user->email_confirmed, $user->terms_accepted);
return pick($decision,
    'true' => redirect('/dashboard'),
    'false' => back()->with('error', 'Requisitos no cumplidos'),
    'tie' => redirect('/verificacion-pendiente')
);
```

### Escenario 2: Feature Flags con Despliegue Gradual
**❌ Antes (condicionales complejas)**
```php
$puedeAccederBeta = false;

if ($user->is_beta_tester) {
    $puedeAccederBeta = true;
} elseif ($user->plan === 'enterprise' && $feature->rollout_percent > 50) {
    $puedeAccederBeta = rand(1, 100) <= $feature->rollout_percent;
} elseif ($feature->enabled === null) {
    // ¿Qué significa null? El estado desconocido causa bugs
    $puedeAccederBeta = false;
}

if ($puedeAccederBeta) {
    return view('beta.dashboard');
} else {
    return view('standard.dashboard');
}
```

**✅ Después (motor de decisión Trilean)**
```php
$estado = ternary_match(
    consensus(
        $user->is_beta_tester,
        $user->plan === 'enterprise' && $feature->rollout_percent > 50,
        $feature->enabled
    ),
    [
        'true' => 'otorgado',
        'false' => 'denegado',
        'unknown' => 'esperando_rollout'
    ]
);

return when_ternary(
    $estado,
    onTrue: fn() => view('beta.dashboard'),
    onFalse: fn() => view('standard.dashboard'),
    onUnknown: fn() => view('pending.dashboard')
);
```

### Escenario 3: Flujo de Aprobación
**❌ Antes (condicionales anidadas)**
```php
if (!$doc->legal_approved) {
    return ['status' => 'pendiente', 'motivo' => 'revisión legal'];
}

if (!$doc->finance_approved) {
    return ['status' => 'pendiente', 'motivo' => 'revisión financiera'];
}

if (!$doc->manager_approved) {
    return ['status' => 'pendiente', 'motivo' => 'aprobación del gerente'];
}

// Todos aprobados - ¿pero qué si uno es null?
return ['status' => 'publicado'];
```

**✅ Después (consenso ponderado Trilean)**
```php
$estado = collect([
    'legal' => $doc->legal_approved,
    'finanzas' => $doc->finance_approved,
    'gerente' => $doc->manager_approved,
])->ternaryWeighted([5, 3, 2]); // Legal tiene más peso

return ternary_match($estado, [
    'true' => ['status' => 'publicado', 'aprobado_por' => 'todos'],
    'false' => ['status' => 'rechazado', 'motivo' => 'fallo_aprobacion'],
    'unknown' => ['status' => 'en_revision', 'departamentos_pendientes' => $this->getDepartamentosPendientes()],
]);
```

---

## 📚 Recursos Técnicos

Vea la documentación detallada en inglés para ejemplos completos de cada recurso: [English Guide](./ternary-guide.en.md)

### 1. 🔥 Helpers Globales (10 funciones)
- `ternary()` - Conversión inteligente a TernaryState
- `maybe()` - Ramificación en tres vías
- `trilean()` - Acceso al servicio principal
- `ternary_vector()` - Operaciones matemáticas en colecciones
- `all_true()` / `any_true()` - Puertas lógicas
- `none_false()` - Garantizar ausencia de FALSE
- `consensus()` - Decisiones democráticas
- `when_ternary()` - Ejecución condicional
- `ternary_match()` - Pattern matching

### 2. 💎 Macros de Collection (12 métodos)
- `ternaryConsensus()` / `ternaryMajority()`
- `whereTernaryTrue/False/Unknown()`
- `ternaryWeighted()` - Votación ponderada
- `ternaryMap()` - Mapeo ternario
- `ternaryScore()` - Puntuación balanceada
- `allTernaryTrue()` / `anyTernaryTrue()`
- `partitionTernary()` - División en tres grupos
- `ternaryGate()` - Puertas lógicas flexibles

### 3. 🗄️ Scopes Eloquent (8 métodos)
- `whereTernaryTrue/False/Unknown()`
- `orderByTernary()` - Ordenación inteligente
- `whereAllTernaryTrue()` / `whereAnyTernaryTrue()`
- `ternaryConsensus()`

### 4. 🌐 Macros de Request (5 métodos)
- `ternary()` - Normalización de entrada
- `hasTernaryTrue/False/Unknown()`
- `ternaryGate()` - Validación multi-campo
- `ternaryExpression()`

### 5. 🎨 Directivas Blade (10+)
- `@ternaryTrue/False/Unknown`
- `@ternaryMatch` - Pattern matching en plantillas
- `@allTrue` / `@anyTrue`
- `@ternaryBadge` / `@ternaryIcon`

### 6. �️ Middleware
- `TernaryGateMiddleware` - Protección de rutas con lógica ternaria

### 7. ✅ Reglas de Validación
- Básicas: `ternary`, `ternary_true`, `ternary_not_false`
- Avanzadas: `ternary_gate`, `ternary_consensus`, `ternary_weighted`

### 8. 🧮 Recursos Avanzados
- Motor de Decisiones con blueprints
- Aritmética ternaria balanceada
- Circuit Builder

---

## 📖 Documentación Detallada

- **[Helpers Globales](./es/helpers-globales.md)** - Todas las 10 funciones helper con ejemplos
- **[Macros de Collection](./es/macros-coleccion.md)** - 12 métodos Collection para lógica ternaria
- **[Scopes Eloquent](./es/scopes-eloquent.md)** - Consultas de base de datos con estados ternarios
- **[Macros de Request](./es/macros-request.md)** - Manejo ternario de peticiones HTTP
- **[Directivas Blade](./es/directivas-blade.md)** - Directivas de plantilla para vistas
- **[Reglas de Validación](./es/reglas-validacion.md)** - Validación de formularios con lógica ternaria
- **[Middleware](./es/middleware.md)** - Protección de rutas con gates ternarios
- **[Capacidades Avanzadas](./es/capacidades-avanzadas.md)** - Motor de Decisiones, Aritmética, Circuitos
- **[Casos de Uso](./es/casos-uso.md)** - Patrones de implementación del mundo real

---

## 🚀 Instalación

```bash
composer require vinkius-labs/trilean
```

### Publicar Configuración
```bash
php artisan vendor:publish --tag=trilean-config
```

### Configurar (opcional)
```php
// config/trilean.php
return [
    'metrics' => [
        'enabled' => env('TRILEAN_METRICS', false),
    ],
    'ui' => [
        'badge_classes' => [
            'true' => 'badge-success',
            'false' => 'badge-danger',
            'unknown' => 'badge-warning',
        ],
    ],
];
```

---

## 📄 Licencia

Licencia MIT - vea el archivo [LICENSE](../LICENSE) para detalles.

---

**Construido con ❤️ por VinkiusLabs** | [GitHub](https://github.com/vinkius-labs/trilean) | [Issues](https://github.com/vinkius-labs/trilean/issues)

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
