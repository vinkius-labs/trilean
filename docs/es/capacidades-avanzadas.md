# 🧮 Capacidades Avanzadas de Trilean

> Funcionalidades para escenarios complejos: aritmética, circuitos lógicos y conversiones balanceadas.

## TernaryArithmetic
### Resumen
Clase utilitaria para operar enteros usando ternario balanceado (`-1`, `0`, `+1`). Ideal en finanzas, scoring y algoritmos de IA simbólica.

### Métodos Clave
| Método | Descripción |
| --- | --- |
| `add(int|string $a, int|string $b)` | Suma valores balanceados (arrays o strings) |
| `subtract($a, $b)` | Resta con normalización |
| `toBalanced(int $value)` | Convierte entero a representación balanceada |
| `fromBalanced(iterable|string $trits)` | Vuelve a entero |
| `normalize(iterable $trits)` | Ajusta carries al formato canónico |

### Ejemplo
```php
$calc = new TernaryArithmetic();
$balanced = $calc->toBalanced(42);
$result = $calc->add($balanced, '-+0');
```

## CircuitBuilder
### Propósito
Construir DAGs de decisión ternaria con una interfaz fluida, útil para pipelines de negocio y simulaciones.

### API
| Método | Función |
| --- | --- |
| `input(string $name, callable|mixed $source)` | Define entradas |
| `gate(string $name, string $operator, array $dependencies, array $options = [])` | Agrega puertas |
| `emit(string $name, string $fromNode)` | Marca salidas |
| `report()` | Retorna `TernaryDecisionReport` con trazas |
| `toGraphviz()` | Exporta grafo para diagramas |

```php
$builder = CircuitBuilder::make()
    ->input('legal', fn () => ternary($doc->legal_state))
    ->input('finance', fn () => ternary($doc->finance_state))
    ->gate('compliance', 'consensus', ['legal', 'finance'], ['requiredRatio' => 0.6])
    ->emit('ready_to_publish', 'compliance');

$decision = $builder->resolve('ready_to_publish');
```

## Conversor BalancedTrit
### Objetivo
Convertir entre representaciones ternarias balanceadas (unicode `−`, ASCII `-`, aliases `POS/NEG/ZERO`).

### Características
- Acepta strings, arrays y números.
- Configurable vía `config('trilean.converters')`.
- Round-trip seguro (`encode` -> `decode`).

```php
$converter = app(BalancedTernaryConverter::class);
$encoded = $converter->encode([-1, 0, 1]);
$decoded = $converter->decode('−0+');
```

## Integraciones
- Combina `CircuitBuilder` + `TernaryArithmetic` para simular impacto financiero con decisiones condicionales.
- Usa el conversor para exportar datos a sistemas legados (`T`, `F`, `U`).

## Buenas Prácticas
- Documenta circuitos complejos con `toGraphviz()` y adjunta diagramas en PRs.
- Estandariza formato de exportación (ASCII vs Unicode) para logs.
- Cachea resultados de `CircuitBuilder` usando `encodedVector` como clave.

> Estas capacidades llevan a Trilean más allá de simples `if`, permitiendo automatización avanzada en compliance, finanzas y operaciones.
