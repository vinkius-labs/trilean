# 💎 Collection Macros Trilean (12 métodos)

> Amplie `Illuminate\Support\Collection` com operações ternárias declarativas que mantêm pipelines legíveis, performáticos e fáceis de auditar.

## Visão Geral
As macros são registradas pelo `TernaryLogicServiceProvider` durante o boot e ficam disponíveis em qualquer `Collection` (inclusive `LazyCollection` quando compatível). Elas usam os helpers globais e o `TernaryLogicService` por baixo dos panos, oferecendo consistência entre controllers, jobs e pipelines de dados.

## Tabela de Referência
| Macro | Tipo de Retorno | Uso Principal |
| --- | --- | --- |
| `ternaryConsensus()` | `TernaryState` | Unificar votos/estados |
| `ternaryMajority()` | `TernaryState` | Decidir por maioria simples |
| `whereTernaryTrue()` | `Collection` | Filtrar itens `TRUE` |
| `whereTernaryFalse()` | `Collection` | Filtrar itens `FALSE` |
| `whereTernaryUnknown()` | `Collection` | Filtrar itens `UNKNOWN` |
| `ternaryWeighted(array $weights)` | `TernaryState` | Decisão ponderada |
| `ternaryMap(callable $callback)` | `TernaryVector` | Transformar/normalizar items |
| `ternaryScore()` | `int` | Métrica balanceada (+1/0/-1) |
| `allTernaryTrue()` | `bool` | Portas AND |
| `anyTernaryTrue()` | `bool` | Portas OR |
| `partitionTernary()` | `array<Collection>` | Segmentação em 3 grupos |
| `ternaryGate(array $options)` | `TernaryState` | Operadores AND/OR/XOR/consensus |

## Detalhamento

### 1. `ternaryConsensus()`
- **Fluxo interno**: Normaliza cada item com `ternary()` e usa `TernaryLogicService::consensus`.
- **Uso típico**:
  ```php
  $decision = $votes->ternaryConsensus();
  ```
- **Quando aplicar**: boards de aprovação, health checks com thresholds.
- **Observabilidade**: combine com `mapWithKeys` para preservar identificadores antes da votação.

### 2. `ternaryMajority()`
- **Descrição**: Atalho para maioria simples (sem pesos). Empate resulta em `UNKNOWN`.
- **Exemplo**:
  ```php
  $state = collect([$nodeA, $nodeB, $nodeC])->ternaryMajority();
  ```
- **Dica**: Use em clusters ou failovers distribuídos.

### 3–5. `whereTernary*()`
- **Implementação**: Usa `data_get` + `ternary()` comparando com `isTrue/False/Unknown`.
- **Assinatura**: `whereTernaryTrue(string $key)` (mesmo para outros estados).
- **Exemplo**:
  ```php
  $aprovados = $requests->whereTernaryTrue('decision.state');
  ```
- **Benefício**: Evita duplicar normalização e facilita auditoria.

### 6. `ternaryWeighted(array $weights)`
- **Objetivo**: Resolver decisões com pesos por posição ou chave.
- **Detalhes**:
  - Aceita arrays associativos (`['legal' => 5, 'finance' => 3]`).
  - Quando pesos faltam, assume 1.
- **Exemplo**:
  ```php
  $resultado = $checks->ternaryWeighted([
      'gateway' => 5,
      'fallback' => 2,
      'manual' => 1,
  ]);
  ```
- **Padrão**: Retorna `TernaryDecisionReport` se `returnReport` for `true`.

### 7. `ternaryMap(callable $callback)`
- **Descrição**: Similar ao `map`, porém força normalização do retorno e converte em `TernaryVector`.
- **Assinatura**: `ternaryMap(fn ($value, $key) => mixed): TernaryVector`
- **Exemplo**:
  ```php
  $vector = $signals->ternaryMap(fn ($signal) => $signal->status);

  if ($vector->majority()->isFalse()) {
      dispatch(new TriggerIncident);
  }
  ```
- **Vantagem**: Encadeie com `sum`, `weighted`, `encode` sem perder imutabilidade.

### 8. `ternaryScore()`
- **Conceito**: Converte cada estado para +1 (`TRUE`), 0 (`UNKNOWN`), -1 (`FALSE`), retornando soma inteira.
- **Uso**: Métricas, dashboards, thresholds.
- **Exemplo**:
  ```php
  $score = $checks->ternaryScore();

  if ($score < 0) {
      Alert::critical('Mais falhas que sucessos');
  }
  ```

### 9–10. `allTernaryTrue()` / `anyTernaryTrue()`
- **Funcionalidade**: Operam sobre a coleção inteira, idênticas aos helpers globais, mas usando os itens já carregados.
- **Exemplo**:
  ```php
  if ($conditions->allTernaryTrue()) {
      // safe deploy
  }
  ```
- **Observação**: Aceitam callback opcional para acessar campos específicos (`$collection->allTernaryTrue(fn ($item) => $item->state)`).

### 11. `partitionTernary()`
- **Retorno**: Array com índices `'true'`, `'false'`, `'unknown'`, cada qual uma Collection independente.
- **Exemplo**:
  ```php
  ['true' => $ok, 'false' => $bad, 'unknown' => $pending] = $inventory->partitionTernary('health');
  ```
- **Aplicações**: Painéis, geração de relatórios, reprocessamento incremental.

### 12. `ternaryGate(array $options = [])`
- **Objetivo**: Aplicar operadores customizados (`and`, `or`, `xor`, `consensus`, `weighted`).
- **Assinatura**: `ternaryGate(string|callable $operator = 'and', array $options = [])`
- **Exemplo**:
  ```php
  $state = $signals->ternaryGate('weighted', [
      'weights' => [5, 3, 2],
      'requiredRatio' => 0.6,
  ]);
  ```
- **Poder extra**: Aceita closures personalizadas recebendo uma instância de `TernaryVector`.

## Padrões de Projeto Recomendados
- **Pipeline de Dados**: use `map -> ternaryMap -> ternaryGate` para evitar condicionais.
- **Aggregate Roots**: em projetos DDD, exponha métodos que retornam `Collection` + macros para compor decisões.
- **Jobs**: snapshots de decisões podem ser serializados via `ternaryMap()->encoded()`.

## Boas Práticas
- Normalize dados externos antes (`map(fn => ternary($value))`).
- Documente as chaves usadas em macros (`whereTernary*`).
- Não abuse de `ternaryGate` com operadores diferentes no mesmo método: extraia para funções nomeadas.

## Testes
- Falsifique coleções com `collect([...])` e estados via `TernaryState::true()` etc.
- Para macros que acessam chaves (`whereTernaryTrue`), use `DTOs` ou `Collection::make` com arrays.
- Utilize `tap` + snapshots em `ternaryMap` para garantir que a normalização ocorra.

## Observabilidade
- Encadeie com `map(fn ($item) => [$item->id, ternary($item->state)])` para logar decisões.
- Exportar `ternaryMap()->toBits()` ajuda a rastrear regressões em testes de carga.

> As macros transformam coleções em DSLs declarativas, mantendo clareza mesmo com volumes altos de regras e estados.
