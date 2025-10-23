# Trilean - Laravel Package

**[English](#english)** | **[Português](#português)** | **[Español](#español)**

---

## English

### What is Trilean?

Trilean pushes ternary logic beyond the classic `true/false/unknown` paradigm. Inspired by balanced-ternary computing (trits, three-state logic gates, arithmetic), the package delivers:

### What is Trilean?

Trilean pushes ternary logic beyond the classic `true/false/unknown` paradigm. Inspired by balanced-ternary computing (trits, three-state logic gates, arithmetic), the package delivers:

- ✅ **Full Kleene operators** (`AND`, `OR`, `NOT`, `XOR`) with strict three-valued logic semantics
- 🧠 **Balanced ternary converter** to score scenarios (-1, 0, +1) with mathematical fidelity
- 🕸️ **Declarative Decision Engine** to orchestrate ternary decision graphs and produce audit-ready reports
- 🧮 **Ternary vectors** with aggregations, consensus, compression and scoring helpers
- 🧾 **Ternary expression DSL** (`consent AND !risk`) with dynamic context resolution
- 🧱 **Laravel integration**: Facade, helpers, macros, middleware, validation rules and Blade directives
- ⚙️ **Production-ready**: Publishable config, presets (Laravel/Lumen/Octane), artisan installers, Livewire/Inertia assets, TypeScript SDK, playground app
- 📊 **Observability hooks**: Telescope, Horizon, Prometheus, and logging integrations

### Why Trilean?

**Before Trilean:**
```php
// Complex nested conditions with unclear intent
if ($user->verified === true && 
    ($user->consent === null || $user->consent === true) && 
    $riskScore !== 'high') {
    // Proceed, but what about edge cases?
}

// Manual null handling everywhere
$status = $user->active ?? 'unknown';
if ($status === true) {
    // approved
} elseif ($status === false) {
    // denied
} else {
    // pending - easy to miss this case
}
```

**After Trilean:**
```php
use function ternary, trilean;

// Clean, expressive ternary logic
if (trilean()->and($user->verified, $user->consent, '!high_risk')->isTrue()) {
    // Proceed with confidence
}

// Elegant pattern matching
echo ternary_match($user->active, [
    'true' => 'Approved',
    'false' => 'Denied',
    'unknown' => 'Pending Review',
]);
```

### Installation

```bash
composer require vinkius-labs/trilean
```

### Quick Start

```php
use VinkiusLabs\Trilean\Enums\TernaryState;
use function ternary, trilean, maybe;

// Convert any value to ternary state
$state = ternary($user->verified);        // TRUE, FALSE, or UNKNOWN
$state = ternary(null);                    // UNKNOWN
$state = ternary('yes');                   // TRUE
$state = ternary(0);                       // FALSE

// Three-way conditionals
echo maybe($user->consent, 
    'Approved',         // if TRUE
    'Denied',           // if FALSE
    'Pending Review'    // if UNKNOWN
);

// Ternary logic operations
$result = trilean()->and($verified, $consented, $active);
$result = trilean()->or($method1, $method2, $method3);
$result = trilean()->weighted([$signal1, $signal2], [3, 1]);
```

### Real-World Example: User Onboarding

**Before Trilean:**
```php
public function canAccessPremium(User $user): bool
{
    $verified = $user->email_verified_at !== null;
    $subscribed = $user->subscription_active ?? false;
    $trial = $user->trial_ends_at > now();
    
    // Complex logic with edge cases
    if ($verified === false) return false;
    if ($subscribed === true) return true;
    if ($subscribed === null && $trial) return true;
    
    return false; // Default to deny
}
```

**After Trilean:**
```php
use VinkiusLabs\Trilean\Decision\TernaryDecisionEngine;

public function canAccessPremium(User $user): TernaryState
{
    $engine = app(TernaryDecisionEngine::class);
    
    $report = $engine->evaluate([
        'inputs' => [
            'verified' => $user->email_verified_at !== null,
            'subscribed' => $user->subscription_active,
            'trial' => $user->trial_ends_at > now(),
        ],
        'gates' => [
            'access' => [
                'operator' => 'or',
                'operands' => ['subscribed', 'trial'],
                'description' => 'Premium access via subscription or trial',
            ],
            'final' => [
                'operator' => 'and',
                'operands' => ['verified', 'access'],
                'description' => 'Must be verified AND have access',
            ],
        ],
        'output' => 'final',
    ]);
    
    // Returns TernaryState with full audit trail
    return $report->result();
}
```

### Features Overview

#### 1. Ternary State Enum

```php
use VinkiusLabs\Trilean\Enums\TernaryState;

$state = TernaryState::TRUE;
$state = TernaryState::FALSE;
$state = TernaryState::UNKNOWN;

// Conversion from mixed values
TernaryState::fromMixed(true);      // TRUE
TernaryState::fromMixed(null);      // UNKNOWN
TernaryState::fromMixed('yes');     // TRUE
TernaryState::fromMixed(1);         // TRUE
TernaryState::fromMixed(-1);        // UNKNOWN
```

#### 2. Global Helpers

```php
// ternary() - convert any value
$state = ternary($value);

// maybe() - three-way conditional
$result = maybe($condition, $ifTrue, $ifFalse, $ifUnknown);

// all_true() - strict AND
if (all_true($verified, $consented, $active)) { }

// any_true() - relaxed OR
if (any_true($method1, $method2, $method3)) { }

// when_ternary() - execute callbacks
when_ternary($state, 
    onTrue: fn() => $user->activate(),
    onFalse: fn() => $user->block(),
    onUnknown: fn() => $user->setPending()
);
```

#### 3. Collection Macros

```php
$votes = collect([true, true, false, null, true]);

$votes->ternaryConsensus();     // Get overall consensus
$votes->ternaryMajority();      // Majority wins
$votes->ternaryScore();         // Balanced score: 2
$votes->whereTernaryTrue('status');
$votes->ternaryWeighted([3, 2, 1, 1, 1]);
```

#### 4. Blade Directives

```blade
@ternary($user->verified)
    <span class="badge badge-success">Verified</span>
@elseternary
    <span class="badge badge-warning">Unverified</span>
@endternary

@ternaryUnknown($user->consent)
    <div class="alert alert-info">Consent pending</div>
@endternaryUnknown

{{ maybe($status, 'Active', 'Inactive', 'Pending') }}

@ternaryBadge($user->verified)
```

#### 5. Validation Rules

```php
$request->validate([
    'kyc_verified' => ['required', 'ternary'],
    'aml_check' => ['required', 'ternary_not_false'],
    'checks' => ['array', 'ternary_gate:and'],
    'decision' => ['ternary_expression:kyc AND aml'],
]);
```

#### 6. Eloquent Scopes

```php
// Add to your model
use VinkiusLabs\Trilean\Traits\HasTernaryState;

class Document extends Model
{
    use HasTernaryState;
    
    protected $casts = [
        'approved' => TernaryState::class,
    ];
}

// Query with ternary scopes
Document::whereTernaryTrue('approved')->get();
Document::whereTernaryUnknown('approved')->get();
```

#### 7. Request Macros

```php
$request->ternary('consent');           // Get as TernaryState
$request->ternaryAny(['kyc', 'aml']);  // Any true?
$request->ternaryAll(['t1', 't2']);    // All true?
```

#### 8. Decision Engine

```php
$engine = app(TernaryDecisionEngine::class);

$report = $engine->evaluate([
    'inputs' => [
        'consent' => $user->consent,
        'risk' => 'metrics.risk_score',
    ],
    'gates' => [
        'compliance' => [
            'operator' => 'and',
            'operands' => ['consent', '!risk'],
        ],
        'final' => [
            'operator' => 'weighted',
            'operands' => ['compliance', 'consent'],
            'weights' => [5, 1],
        ],
    ],
    'output' => 'final',
], [
    'metrics' => ['risk_score' => 'low'],
]);

$report->result();              // TernaryState
$report->encodedVector();       // "+-0"
$report->decisions();           // Full audit trail
$report->toArray();             // Export for logging
```

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=trilean-config
```

Configure metrics, presets, and UI options in `config/trilean.php`:

```php
return [
    'policies' => [
        'unknown_resolves_to' => false,
        'throw_on_unknown' => false,
        'unknown_message' => 'This decision is still pending.',
    ],
    
    'metrics' => [
        'enabled' => env('TRILEAN_METRICS', false),
        'drivers' => [
            'log' => ['channel' => 'stack'],
            'horizon' => ['enabled' => false],
            'telescope' => ['enabled' => true],
            'prometheus' => ['enabled' => true],
        ],
    ],
];
```

### Artisan Commands

```bash
# Install with preset
php artisan trilean:install laravel

# Health check
php artisan trilean:doctor
```

### Testing

```bash
composer test
```

### Documentation

**Complete guides available in multiple languages:**

#### 📘 English Documentation
- [Ternary Logic Guide](docs/ternary-guide.en.md)
- [Global Helpers](docs/en/global-helpers.md)
- [Collection Macros](docs/en/collection-macros.md)
- [Eloquent Scopes](docs/en/eloquent-scopes.md)
- [Request Macros](docs/en/request-macros.md)
- [Blade Directives](docs/en/blade-directives.md)
- [Middleware](docs/en/middleware.md)
- [Validation Rules](docs/en/validation-rules.md)
- [Advanced Capabilities](docs/en/advanced-capabilities.md)
- [Use Cases](docs/en/use-cases.md)
- [Future Ideas](docs/en/future-ideas.md)

#### 📗 Documentação em Português
- [Guia de Lógica Ternária](docs/guia-ternario.pt.md)
- [Helpers Globais](docs/pt/helpers-globais.md)
- [Macros de Collection](docs/pt/macros-coleccion.md)
- [Scopes Eloquent](docs/pt/eloquent-scopes.md)
- [Macros de Request](docs/pt/request-macros.md)
- [Diretivas Blade](docs/pt/blade-directives.md)
- [Middleware](docs/pt/middleware.md)
- [Regras de Validação](docs/pt/validation-rules.md)
- [Recursos Avançados](docs/pt/recursos-avancados.md)
- [Casos de Uso](docs/pt/casos-de-uso.md)
- [Sugestões Futuras](docs/pt/sugestoes-futuras.md)

#### 📙 Documentación en Español
- [Guía de Lógica Ternaria](docs/guia-ternario.es.md)
- [Helpers Globales](docs/es/helpers-globales.md)
- [Macros de Colección](docs/es/macros-coleccion.md)
- [Scopes Eloquent](docs/es/scopes-eloquent.md)
- [Macros de Request](docs/es/macros-request.md)
- [Directivas Blade](docs/es/directivas-blade.md)
- [Middleware](docs/es/middleware.md)
- [Reglas de Validación](docs/es/reglas-validacion.md)
- [Capacidades Avanzadas](docs/es/capacidades-avanzadas.md)
- [Casos de Uso](docs/es/casos-uso.md)
- [Ideas Futuras](docs/es/ideas-futuras.md)

### License

MIT © Renato Marinho

---

## Português

### O que é Trilean?

Trilean expande a lógica ternária além do paradigma clássico `verdadeiro/falso/desconhecido`. Inspirado em computação ternária balanceada (trits, portas lógicas de três estados, aritmética), o pacote oferece:

- ✅ **Operadores Kleene completos** (`AND`, `OR`, `NOT`, `XOR`) com semântica rigorosa de lógica tri-valorada
- 🧠 **Conversor ternário balanceado** para pontuar cenários (-1, 0, +1) com fidelidade matemática
- 🕸️ **Motor de Decisão Declarativo** para orquestrar grafos de decisão ternários e produzir relatórios auditáveis
- 🧮 **Vetores ternários** com agregações, consenso, compressão e helpers de pontuação
- 🧾 **DSL de expressões ternárias** (`consentimento AND !risco`) com resolução dinâmica de contexto
- 🧱 **Integração Laravel**: Facade, helpers, macros, middleware, regras de validação e diretivas Blade
- ⚙️ **Pronto para produção**: Config publicável, presets (Laravel/Lumen/Octane), instaladores artisan, assets Livewire/Inertia, TypeScript SDK, app playground
- 📊 **Hooks de observabilidade**: integrações com Telescope, Horizon, Prometheus e logging

### Por que Trilean?

**Antes do Trilean:**
```php
// Condições aninhadas complexas com intenção pouco clara
if ($user->verified === true && 
    ($user->consent === null || $user->consent === true) && 
    $riskScore !== 'high') {
    // Prosseguir, mas e os casos extremos?
}

// Tratamento manual de null em todo lugar
$status = $user->active ?? 'desconhecido';
if ($status === true) {
    // aprovado
} elseif ($status === false) {
    // negado
} else {
    // pendente - fácil esquecer este caso
}
```

**Depois do Trilean:**
```php
use function ternary, trilean;

// Lógica ternária limpa e expressiva
if (trilean()->and($user->verified, $user->consent, '!high_risk')->isTrue()) {
    // Prosseguir com confiança
}

// Pattern matching elegante
echo ternary_match($user->active, [
    'true' => 'Aprovado',
    'false' => 'Negado',
    'unknown' => 'Em Análise',
]);
```

### Instalação

```bash
composer require vinkius-labs/trilean
```

### Guia Rápido

```php
use VinkiusLabs\Trilean\Enums\TernaryState;
use function ternary, trilean, maybe;

// Converter qualquer valor para estado ternário
$state = ternary($user->verified);        // TRUE, FALSE, ou UNKNOWN
$state = ternary(null);                    // UNKNOWN
$state = ternary('sim');                   // TRUE
$state = ternary(0);                       // FALSE

// Condicionais triplas
echo maybe($user->consent, 
    'Aprovado',         // se TRUE
    'Negado',           // se FALSE
    'Em Análise'        // se UNKNOWN
);

// Operações de lógica ternária
$result = trilean()->and($verified, $consented, $active);
$result = trilean()->or($method1, $method2, $method3);
$result = trilean()->weighted([$signal1, $signal2], [3, 1]);
```

### Documentação Completa

Veja a [documentação completa em português](docs/guia-ternario.pt.md) para guias detalhados.

### Licença

MIT © Renato Marinho

---

## Español

### ¿Qué es Trilean?

Trilean expande la lógica ternaria más allá del paradigma clásico `verdadero/falso/desconocido`. Inspirado en computación ternaria balanceada (trits, puertas lógicas de tres estados, aritmética), el paquete ofrece:

- ✅ **Operadores Kleene completos** (`AND`, `OR`, `NOT`, `XOR`) con semántica rigurosa de lógica tri-valuada
- 🧠 **Conversor ternario balanceado** para puntuar escenarios (-1, 0, +1) con fidelidad matemática
- 🕸️ **Motor de Decisión Declarativo** para orquestar grafos de decisión ternarios y producir informes auditables
- 🧮 **Vectores ternarios** con agregaciones, consenso, compresión y helpers de puntuación
- 🧾 **DSL de expresiones ternarias** (`consentimiento AND !riesgo`) con resolución dinámica de contexto
- 🧱 **Integración Laravel**: Facade, helpers, macros, middleware, reglas de validación y directivas Blade
- ⚙️ **Listo para producción**: Config publicable, presets (Laravel/Lumen/Octane), instaladores artisan, assets Livewire/Inertia, TypeScript SDK, app playground
- 📊 **Hooks de observabilidad**: integraciones con Telescope, Horizon, Prometheus y logging

### ¿Por qué Trilean?

**Antes de Trilean:**
```php
// Condiciones anidadas complejas con intención poco clara
if ($user->verified === true && 
    ($user->consent === null || $user->consent === true) && 
    $riskScore !== 'high') {
    // Proceder, ¿pero qué pasa con los casos extremos?
}

// Manejo manual de null en todas partes
$status = $user->active ?? 'desconocido';
if ($status === true) {
    // aprobado
} elseif ($status === false) {
    // denegado
} else {
    // pendiente - fácil olvidar este caso
}
```

**Después de Trilean:**
```php
use function ternary, trilean;

// Lógica ternaria limpia y expresiva
if (trilean()->and($user->verified, $user->consent, '!high_risk')->isTrue()) {
    // Proceder con confianza
}

// Pattern matching elegante
echo ternary_match($user->active, [
    'true' => 'Aprobado',
    'false' => 'Denegado',
    'unknown' => 'En Revisión',
]);
```

### Instalación

```bash
composer require vinkius-labs/trilean
```

### Guía Rápida

```php
use VinkiusLabs\Trilean\Enums\TernaryState;
use function ternary, trilean, maybe;

// Convertir cualquier valor a estado ternario
$state = ternary($user->verified);        // TRUE, FALSE, o UNKNOWN
$state = ternary(null);                    // UNKNOWN
$state = ternary('sí');                    // TRUE
$state = ternary(0);                       // FALSE

// Condicionales triples
echo maybe($user->consent, 
    'Aprobado',         // si TRUE
    'Denegado',         // si FALSE
    'En Revisión'       // si UNKNOWN
);

// Operaciones de lógica ternaria
$result = trilean()->and($verified, $consented, $active);
$result = trilean()->or($method1, $method2, $method3);
$result = trilean()->weighted([$signal1, $signal2], [3, 1]);
```

### Documentación Completa

Consulte la [documentación completa en español](docs/guia-ternario.es.md) para guías detalladas.

### Licencia

MIT © Renato Marinho

