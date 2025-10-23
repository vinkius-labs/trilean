# 🧮 Trilean Advanced Capabilities

> Power tools for complex scenarios: arithmetic, circuit orchestration, and balanced ternary conversions.

## TernaryArithmetic
### Overview
Utility class for performing arithmetic on integers using balanced ternary (`-1`, `0`, `+1`). Ideal for finance, scoring, and symbolic AI algorithms.

### Key Methods
| Method | Description |
| --- | --- |
| `add(int|string $a, int|string $b)` | Adds balanced numbers (string or iterable input) |
| `subtract($a, $b)` | Subtraction with normalization |
| `toBalanced(int $value)` | Convert integer to balanced representation (array or string) |
| `fromBalanced(iterable|string $trits)` | Convert representation back to integer |
| `normalize(iterable $trits)` | Adjust carries into canonical form |

### Example
```php
$calc = new TernaryArithmetic();
$balanced = $calc->toBalanced(42); // e.g. ['+', '0', '-']
$result = $calc->add($balanced, '-+0');
```

### Use Cases
- Risk scoring (`high = +`, `low = -`).
- Translating legacy boolean algorithms while keeping precision.

## CircuitBuilder
### Purpose
Build ternary decision DAGs fluently—perfect for business pipelines and simulation environments.

### Core API
| Method | Role |
| --- | --- |
| `input(string $name, callable|mixed $source)` | Define inputs |
| `gate(string $name, string $operator, array $dependencies, array $options = [])` | Add gates |
| `emit(string $name, string $fromNode)` | Mark outputs |
| `report()` | Generate `TernaryDecisionReport` with timeline |
| `toGraphviz()` | Export graph for diagrams |

### Example
```php
$builder = CircuitBuilder::make()
    ->input('legal', fn () => ternary($doc->legal_state))
    ->input('finance', fn () => ternary($doc->finance_state))
    ->gate('compliance', 'consensus', ['legal', 'finance'], ['requiredRatio' => 0.6])
    ->emit('ready_to_publish', 'compliance');

$decision = $builder->resolve('ready_to_publish');
```

### Observability
- `report()` returns node history, weights used, execution time.
- `toBlueprint()` serializes circuits for persistence.

## Balanced Trit Converter
### Goal
Convert between balanced ternary representations (unicode `−`, ASCII `-`, aliases `POS/NEG/ZERO`).

### Features
- Accepts strings, arrays, and numbers.
- Configurable via `config('trilean.converters')`.
- Supports round-trip conversions (`encode` -> `decode`).

### Usage
```php
$converter = app(BalancedTernaryConverter::class);
$encoded = $converter->encode([-1, 0, 1]);
$decoded = $converter->decode('−0+');
```

## Integrating Features
- Combine `CircuitBuilder` + `TernaryArithmetic` to simulate financial impact under conditional decisions.
- Use the converter when exporting balanced data to legacy systems (e.g. `T`, `F`, `U`).

## Best Practices
- Document complex circuits via `->toGraphviz()` and attach diagrams to pull requests.
- Standardize export formats (ASCII vs Unicode) to avoid log drift.
- Cache `CircuitBuilder` results using `encodedVector` keys for heavy workloads.

> Advanced features push Trilean beyond simple `if` statements, enabling automation in compliance, finance, and AI-proofing domains.
