# 🧮 Recursos Avançados Trilean

> Funcionalidades para cenários complexos: cálculos matemáticos, circuitos lógicos e conversões balanceadas.

## TernaryArithmetic
### Visão Geral
Classe utilitária que executa operações em inteiros utilizando o sistema ternário balanceado (`-1`, `0`, `+1`). Ideal para finanças, scoring e algoritmos de IA simbólica.

### Principais Métodos
| Método | Descrição |
| --- | --- |
| `add(int|string $a, int|string $b)` | Soma números balanceados (aceita strings codificadas) |
| `subtract($a, $b)` | Subtração com normalização |
| `toBalanced(int $value)` | Converte inteiro para representação balanceada (array ou string) |
| `fromBalanced(iterable|string $trits)` | Converte representação para inteiro |
| `normalize(iterable $trits)` | Ajusta carries para formato canônico |

### Exemplo
```php
$calc = new TernaryArithmetic();
$balanced = $calc->toBalanced(42); // retorna ['+', '0', '-'] etc.
$result = $calc->add($balanced, '-+0');
```

### Casos de Uso
- Ajustar pontuação de risco (`risco_alto = +`, `risco_baixo = -`).
- Converter algoritmos booleanos legados para fluxo ternário mantendo precisão.

## CircuitBuilder
### Propósito
Construir grafos acíclicos de decisões ternárias com interface fluente, útil para pipelines de negócios e simulação.

### API Essencial
| Método | Função |
| --- | --- |
| `input(string $name, callable|mixed $source)` | Define entradas |
| `gate(string $name, string $operator, array $dependencies, array $options = [])` | Define portas |
| `emit(string $name, string $fromNode)` | Marca saídas |
| `report()` | Gera `TernaryDecisionReport` com trilha |
| `toGraphviz()` | Exporta grafo para diagramas |

### Exemplo
```php
$builder = CircuitBuilder::make()
    ->input('legal', fn () => ternary($doc->legal_state))
    ->input('finance', fn () => ternary($doc->finance_state))
    ->gate('compliance', 'consensus', ['legal', 'finance'], ['requiredRatio' => 0.6])
    ->emit('ready_to_publish', 'compliance');

$decision = $builder->resolve('ready_to_publish');
```

### Observabilidade
- `report()` retorna histórico de cada nó, pesos utilizados, tempo de execução.
- `toBlueprint()` serializa circuito para persistência e reuso.

## Conversor BalancedTrit
### Objetivo
Converter entre diferentes representações ternárias (unicode `−`, ascii `-`, aliases `POS/NEG/ZERO`).

### Características
- Aceita strings, arrays e números.
- Configurável via `config('trilean.converters')`.
- Suporta round-trip (`encode` -> `decode`).

### Uso
```php
$converter = app(BalancedTernaryConverter::class);
$encoded = $converter->encode([-1, 0, 1]); // retorna '--0+'
$decoded = $converter->decode('−0+');
```

## Integração Entre Recursos
- Combine `CircuitBuilder` + `TernaryArithmetic` para simular impacto financeiro com decisões condicionais.
- Use o conversor em exportações para sistemas legados (padrões `T`, `F`, `U`).

## Boas Práticas
- Documente circuitos complexos com `->toGraphviz()` e anexe aos PRs.
- Padronize formato de exportação (ASCII vs Unicode) para evitar divergências em logs.
- Ao lidar com altas cargas, cache resultados do `CircuitBuilder` usando `encodedVector` como chave.

> Os recursos avançados oferecem ferramentas para dominar cenários além dos ifs tradicionais, levando o Trilean a domínios de automação mais sofisticados.
