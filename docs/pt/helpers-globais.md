# 🔥 Helpers Globais Trilean

> Referência completa dos helpers registrados pelo pacote para acelerar fluxos ternários no Laravel.

## Visão Geral
Os helpers globais expõem operações comuns da `TernaryLogicService` através de funções idiomáticas em PHP. Eles simplificam condicionais, normalizações e decisões complexas, mantendo o código expressivo e testável. Todos estão disponíveis após registrar o `TernaryLogicServiceProvider`.

## Tabela Rápida
| Helper | Assinatura | Retorno | Uso Primário |
| --- | --- | --- | --- |
| `ternary()` | `mixed $value, ?string $field = null` | `TernaryState` | Normalização consistente |
| `maybe()` | `mixed $value, array $callbacks = []` | `mixed` | Controle de fluxo sem `if`|
| `trilean()` | `void` | `TernaryLogicService` | Acesso ao serviço principal |
| `ternary_vector()` | `iterable $values` | `TernaryVector` | Operações matemáticas |
| `all_true()` | `mixed ...$values` | `bool` | Portas lógicas tipo AND |
| `any_true()` | `mixed ...$values` | `bool` | Portas OR |
| `none_false()` | `mixed ...$values` | `bool` | Garantir ausência de FALSE |
| `when_ternary()` | `mixed $value, array $callbacks` | `mixed` | Execução lazy por estado |
| `consensus()` | `iterable $values, array $options = []` | `TernaryState` | Votações e quóruns |
| `ternary_match()` | `mixed $value, array $map, mixed $default = null` | `mixed` | Pattern matching claro |

## Detalhamento por Helper

### `ternary()`
- **Objetivo**: Converter qualquer valor em uma instância `TernaryState` (`true`, `false`, `unknown`).
- **Assinatura real**: `function ternary(mixed $value, ?string $field = null, array $context = []): TernaryState`
- **Como funciona**: Encaminha para `TernaryState::fromMixed`, aplicando heurísticas (booleanos, inteiros, strings, `null`, enums, atributos Eloquent).
- **Quando usar**:
  - Normalizar inputs de formulários antes de salvar.
  - Criar pipelines em Collections para manter coerência.
  - Serializar valores ternários em logs.
- **Exemplo**:
  ```php
  $state = ternary($request->input('risk_level'));

  if ($state->isUnknown()) {
      return response()->json(['status' => 'awaiting-data']);
  }
  ```
- **Boas práticas**:
  - Passe `$field` para mensagens de erro amigáveis.
  - Combine com `data_get` para valores aninhados.
  - Documente o contrato esperado pela equipe.

### `maybe()`
- **Objetivo**: Criar ramificações ternárias declarativas sem espalhar `if/else`.
- **Assinatura real**: `function maybe(mixed $value, array $callbacks = [], mixed $fallback = null)`
- **Mapeamento de callbacks**: `'true'`, `'false'`, `'unknown'` (chaves obrigatórias). Suporte adicional para `'any'` (pós-processamento) e `'default'`.
- **Exemplo básico**:
  ```php
  return maybe($featureFlag, [
      'true' => fn () => $this->enablePremium(),
      'false' => fn () => $this->logSkip('flag disabled'),
      'unknown' => fn () => $this->queueReview(),
      'any' => fn ($state) => Metrics::record('flags.checked', $state->name),
  ]);
  ```
- **Pontos de atenção**:
  - Callbacks são executados lazy; evite side effects fora deles.
  - Retornos podem ser heterogêneos, mas mantenha consistência.
  - Combine com `Report` para métricas.

### `trilean()`
- **Objetivo**: Obter o serviço principal sem acoplarem-se ao container manualmente.
- **Assinatura**: `function trilean(): TernaryLogicService`
- **Usos comuns**:
  - Resolver operações avançadas (`xor`, `weighted`, `consensus`).
  - Facilitar mocks em testes (substituindo binding no container).
- **Exemplo**:
  ```php
  $logic = trilean();
  $result = $logic->xor($inputA, $inputB);
  ```

### `ternary_vector()`
- **Objetivo**: Encapsular vetores ternários e obter APIs matemáticas/coletivas.
- **Assinatura**: `function ternary_vector(iterable $values, array $options = []): TernaryVector`
- **Capacidades**:
  - `sum()`, `average()`, `majority()`, `weighted()`.
  - Exporta para `encoded()` (string), `toArray()`, `toBits()`.
- **Exemplo**:
  ```php
  $vector = ternary_vector([$sensorA, $sensorB, $sensorC]);

  if ($vector->majority()->isTrue()) {
      dispatch(new ActivateFailover);
  }
  ```
- **Erros comuns**:
  - Passar valores não normalizados. Sempre normalize antes se a origem for heterogênea.
  - Confundir pesos com índices (usar arrays associativos permite clareza).

### `all_true()`
- **Objetivo**: Atalho AND com coerção ternária.
- **Assinatura**: `function all_true(mixed ...$values): bool`
- **Implementação**: `return trilean()->and(...)->isTrue();`
- **Uso típico**:
  ```php
  if (all_true($user->verified, $user->hasTwoFactorEnabled(), !$user->blocked)) {
      // liberar recurso
  }
  ```
- **Notas**:
  - Converte internamente para `TernaryState`.
  - Retorna `false` se qualquer valor for `FALSE` ou `UNKNOWN`.

### `any_true()`
- **Objetivo**: Atalho OR.
- **Comportamento**: Retorna `true` se ao menos um valor resultar `TRUE`.
- **Exemplo**:
  ```php
  if (any_true($pipeline->cachedDecision(), $realTimeState)) {
      return response()->ok();
  }
  ```
- **Insights**:
  - Se todos forem `UNKNOWN`, retorna `false` (preferindo segurança).
  - Combine com `ternary_vector()->score()` para métricas.

### `none_false()`
- **Objetivo**: Certificar que nenhum participante vetou a decisão.
- **Exemplo**:
  ```php
  if (none_false($policy->legal, $policy->compliance, $policy->security)) {
      Approvals::record($policy);
  }
  ```
- **Detalhes**:
  - Usa `TernaryLogicService::noneFalse` garantindo que `UNKNOWN` seja permitido.
  - Ideal para fluxos que aceitam incerteza mas não veto explícito.

### `when_ternary()`
- **Objetivo**: Encapsular side effects por estado com fallback.
- **Assinatura**: `function when_ternary(mixed $value, array $callbacks, mixed $default = null)`
- **Uso**:
  ```php
  when_ternary($deploymentStatus, [
      'true' => fn () => Notifier::success('Deploy estável'),
      'false' => fn () => Notifier::critical('Rollback necessário'),
      'unknown' => fn () => Notifier::warning('Monitorando'),
  ]);
  ```
- **Recomendação**: Ideal em observers, listeners e middlewares.

### `consensus()`
- **Objetivo**: Resolver votações e quóruns com possibilidade de empate.
- **Assinatura**: `function consensus(iterable $values, array $options = []): TernaryState`
- **Opções**: `requiredRatio`, `weights`, `tieBreakers`.
- **Exemplo**:
  ```php
  $decision = consensus([
      'legal' => $doc->legal_state,
      'finance' => $doc->finance_state,
      'ops' => $doc->ops_state,
  ], options: [
      'weights' => ['legal' => 3, 'finance' => 2, 'ops' => 1],
      'requiredRatio' => 0.66,
  ]);
  ```
- **Vantagens**:
  - Evita duplicar lógica de combinações.
  - Transparente para auditorias (usar `TernaryDecisionReport`).

### `ternary_match()`
- **Objetivo**: Mapear estados para saídas humanas ou técnicas.
- **Assinatura**: `function ternary_match(mixed $value, array $map, mixed $default = null)`
- **Exemplo**:
  ```php
  $label = ternary_match($device->health_state, [
      'true' => __('device.status.ok'),
      'false' => __('device.status.down'),
      'unknown' => __('device.status.degraded'),
  ]);
  ```
- **Extras**:
  - Suporta closures como valores do mapa.
  - Quando `$map['any']` existe, é usado para pós-processamento do retorno.

## Estratégias de Uso Combinado
- **Feature Flags**: `when_ternary()` aplica side effects, `any_true()` determina fallback, `ternary_match()` exibe UI.
- **APIs BFF**: Normalize requests com `ternary()`, combine com `consensus()` antes de enviar a decisão.
- **Logs e métricas**: Use `TernaryDecisionReport` e helpers para exportar valores padronizados.

## Testes e Debug
- Mocke o serviço via `app()->instance(TernaryLogicService::class, $fake)` para isolar comportamentos.
- Ao testar helpers puros, use `TernaryState::true()`, `::false()`, `::unknown()` para asserts explícitos.
- Para cobertura extra, registre listeners a eventos lançados pelo serviço e valide side effects.

## Checklist de Adoção
- [ ] Substituiu condicionais críticas por helpers?
- [ ] Documentou contratos esperados para inputs ternários?
- [ ] Configurou métricas/observabilidade para decisões?
- [ ] Criou testes cobrindo estados `UNKNOWN`?

> Use os helpers como a camada base; eles reduzem divergências e aceleram todos os outros recursos do ecossistema Trilean.
