# Laravel Trilean

> **Author:** Renato Marinho • **Repository:** [VinkiusLabs/trilean](https://github.com/VinkiusLabs/trilean)

Trilean pushes ternary logic beyond the classic `true/false/unknown`. Inspired by balanced-ternary computing (trits, three-state logic gates, arithmetic), the package delivers:

- ✅ Full Kleene operators (`AND`, `OR`, `NOT`, `XOR`) with strict semantics
- 🧠 Balanced ternary converter to score scenarios (-1, 0, +1) with mathematical fidelity
- 🕸️ Declarative **Decision Engine** to orchestrate ternary decision graphs and produce audit-ready reports
- 🧮 Ternary vectors with aggregations, consensus, compression and scoring helpers
- 🧾 Ternary expression DSL (`consent AND !risk`) with dynamic context resolution
- 🧱 Facade, helpers, macros, middleware, validation rules and Blade directives ready for controllers, policies, jobs and pipelines
- ⚙️ First-class Laravel integration: publishable config, presets (Laravel/Lumen/Octane), artisan installers, Livewire/Inertia assets, TypeScript SDK snippet, playground app and observability hooks (Telescope, Horizon, Prometheus, logging)

## Installation

```bash
composer require VinkiusLabs/trilean
```

Local path repository setup:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../trilean/packages/VinkiusLabs/Trilean"
    }
  ]
}
```

```bash
composer require VinkiusLabs/trilean:@dev
```

## Installation Steps (Laravel)

```bash
php artisan vendor:publish --tag=trilean-config
php artisan trilean:install laravel --playground
```

The installer publishes configuration, UI assets, Livewire/Inertia stubs, TypeScript helpers, and an optional playground application. Re-run with `--force` to overwrite files, or switch presets (`lumen`, `octane`).

## Quickstart

```php
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Services\TernaryLogicService;

$logic = app(TernaryLogicService::class);

$decision = $logic->and('yes', null);       // => UNKNOWN
$fallback = $logic->or(false, 'unknown');   // => FALSE
$encoded  = $logic->encode([$decision, $fallback]); // => "+-"
```

## Ternary Vectors & Balanced Ternary

```php
use VinkiusLabs\Trilean\Collections\TernaryVector;
use VinkiusLabs\Trilean\Support\BalancedTernaryConverter;

$vector = TernaryVector::make([true, 'unknown', false, true]);

$vector->consensus();     // => TernaryState::UNKNOWN
$vector->majority();      // => TernaryState::TRUE
$vector->score();         // => 1 (balanced ternary score)

$converter = app(BalancedTernaryConverter::class);
$converter->toBalanced(42); // => "+-0"
```

## Decision Engine

```php
$engine = app('trilean.decision');

$report = $engine->evaluate([
    'inputs' => [
        'consent' => '@user.consent AND !user.fraud',
        'risk' => 'metrics.risk',
    ],
    'gates' => [
        'compliance' => [
            'operator' => 'and',
            'operands' => ['consent', '!risk'],
        ],
        'final' => [
            'operator' => 'weighted',
            'operands' => ['compliance', 'risk'],
            'weights' => [4, -3],
        ],
    ],
    'output' => 'final',
], context: [
    'user' => ['consent' => 'true', 'fraud' => null],
    'metrics' => ['risk' => 'unknown'],
]);

$report->result()->label();      // => "Unknown"
$report->encodedVector();        // => "0+"
$report->toArray()['decisions']; // => audit trail for the decision graph
```

## Ternary DSL

```php
$result = Trilean::expression('feature AND !risk OR override', [
    'feature' => true,
    'risk' => 'unknown',
    'override' => false,
]);
```

## Artisan Commands

- `php artisan trilean:install {preset}` — publish configuration/resources and apply opinionated presets.
- `php artisan trilean:doctor` — run health checks (helpers, macros, policies, metrics drivers, config).

## Observability & Metrics

The package can emit decision telemetry to logging channels, Laravel Horizon, Telescope, and Prometheus. Enable drivers via `config/trilean.php` and consume `TernaryDecisionEvaluated` events or `TernaryMetrics` helper methods to record custom signals.

## Documentation Index

### English
- [Quick guide](docs/ternary-guide.en.md)
- Deep dives in `docs/en/`: [Global helpers](docs/en/global-helpers.md), [Collection macros](docs/en/collection-macros.md), [Eloquent scopes](docs/en/eloquent-scopes.md), [Request macros](docs/en/request-macros.md), [Blade directives](docs/en/blade-directives.md), [Middleware](docs/en/middleware.md), [Validation rules](docs/en/validation-rules.md), [Advanced capabilities](docs/en/advanced-capabilities.md), [Use cases](docs/en/use-cases.md), [Future ideas](docs/en/future-ideas.md)

### Português
- [Guia inicial](docs/guia-ternario.pt.md)
- Conteúdo aprofundado em `docs/pt/`: [Helpers globais](docs/pt/helpers-globais.md), [Macros de collection](docs/pt/collection-macros.md), [Scopes Eloquent](docs/pt/eloquent-scopes.md), [Macros de request](docs/pt/request-macros.md), [Diretivas Blade](docs/pt/blade-directives.md), [Middleware](docs/pt/middleware.md), [Regras de validação](docs/pt/validation-rules.md), [Recursos avançados](docs/pt/recursos-avancados.md), [Casos de uso](docs/pt/casos-de-uso.md), [Sugestões futuras](docs/pt/sugestoes-futuras.md)

### Español
- [Guía rápida](docs/guia-ternario.es.md)
- Profundización en `docs/es/`: [Helpers globales](docs/es/helpers-globales.md), [Macros de colección](docs/es/macros-coleccion.md), [Scopes Eloquent](docs/es/scopes-eloquent.md), [Macros de request](docs/es/macros-request.md), [Directivas Blade](docs/es/directivas-blade.md), [Middleware](docs/es/middleware.md), [Reglas de validación](docs/es/reglas-validacion.md), [Capacidades avanzadas](docs/es/capacidades-avanzadas.md), [Casos de uso](docs/es/casos-uso.md), [Ideas futuras](docs/es/ideas-futuras.md)

## Docker Environment

Spin up an isolated environment with all package dependencies using the Docker manifests in the project root:

```bash
docker compose build
docker compose up -d
docker compose exec app composer install
docker compose exec app composer test
```

The `app` service mounts the project directory at `/workspace` so you can run Composer, PHPUnit or Artisan commands inside the container without extra setup.

## Tests

```bash
composer test
```

## License

MIT © Renato Marinho
