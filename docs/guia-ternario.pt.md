# 📘 Guía Trilean em Português

## Visão Geral
Trilean oferece uma abordagem de computação ternária para aplicações Laravel. Em vez de alternar apenas entre verdadeiro/falso, cada decisão passa a reconhecer estados `TRUE`, `FALSE` e `UNKNOWN`, evitando condicionais frágeis e estados inconsistentes.

## Antes e Depois
### Cenário: Liberação de um recurso premium
**Antes (booleanos clássicos)**
```php
if ($user->verified && $user->consent && !$user->blocked) {
    return 'liberado';
}

return 'negado';
```

**Depois (Trilean)**
```php
if (all_true($user->verified, $user->consent, !$user->blocked)) {
    return 'liberado';
}

return ternary_match(false, [
    'true' => 'liberado',
    'false' => 'negado',
    'unknown' => 'analisar',
]);
```
A lógica continua enxuta, mas agora o estado desconhecido é tratado explicitamente.

### Cenário: Aprovação multi-etapas
**Antes**
```php
if (!$doc->legal_approved) {
    return 'aguardando jurídico';
}

if (!$doc->finance_approved) {
    return 'aguardando financeiro';
}

return 'publicado';
```

**Depois**
```php
$estado = collect([
    $doc->legal_approved,
    $doc->finance_approved,
    $doc->manager_approved,
])->ternaryWeighted([5, 3, 2]);

return ternary_match($estado, [
    'true' => 'publicado',
    'false' => 'bloqueado',
    'unknown' => 'aguardando conferências',
]);
```
Aqui aplicamos pesos diferenciados e mantemos rastreamento de estado.

## Recursos Técnicos
### 1. 🔥 Helpers Globais (10 funções)
- `ternary()` – Converte qualquer valor para `TernaryState` usando `TernaryState::fromMixed`, garantindo normalização consistente.
- `maybe()` – Implementa ramificação ternária com `match`, aceitando callbacks lazy; ideal para respostas HTTP.
- `trilean()` – Retorna a instância `TernaryLogicService` via service container (`app('trilean.logic')`).
- `ternary_vector()` – Cria `TernaryVector`, que encapsula coleções de estados com operações matemáticas.
- `all_true()` – Avalia com `TernaryLogicService::and` e retorna bool com `isTrue()`.
- `any_true()` – Usa `TernaryLogicService::or` para detectar estados positivos.
- `none_false()` – Garante ausência de `FALSE` combinando `or()` e `and()`.
- `when_ternary()` – Executa callbacks conforme o resultado de `TernaryState`, centralizando efeitos colaterais.
- `consensus()` – Calcula consenso com `TernaryLogicService::consensus`, ótimo para votações.
- `ternary_match()` – Pattern matching que normaliza estados e consulta mapa associativo.

### 2. 💎 Collection Macros (12 métodos)
As macros são registradas em runtime no `ServiceProvider` e permitem operações declarativas:
- `ternaryConsensus()` / `ternaryMajority()` – Empacotam `TernaryVector` para usar consenso ou maioria com pesos iguais.
- `whereTernaryTrue/False/Unknown()` – Filtram coleções com `ternary()` e `data_get`, garantindo compatibilidade com arrays/objetos.
- `ternaryWeighted()` – Chama `trilean()->weighted`, aceitando pesos dinamicamente.
- `ternaryMap()` – Converte o resultado do `map` em `TernaryVector` para operações subsequentes.
- `ternaryScore()` – Soma os valores balanceados (`+1`, `0`, `-1`).
- `allTernaryTrue()` / `anyTernaryTrue()` – Simplificam portas lógicas sem sair da collection.
- `partitionTernary()` – Retorna três coleções independentes, útil para dashboards.
- `ternaryGate()` – Encapsula AND/OR/XOR/consensus usando a infraestrutura do serviço.

### 3. 🗄️ Eloquent Scopes (8 métodos)
- `whereTernaryTrue/False/Unknown()` – Traduzem estados ternários para consultas SQL com `orWhere` e normalização.
- `orderByTernary()` – Usa `CASE` customizado para ordenar priorizando `TRUE` > `UNKNOWN` > `FALSE`.
- `whereAllTernaryTrue()` / `whereAnyTernaryTrue()` – Combinam múltiplas colunas chamando os escopos anteriores.
- `ternaryConsensus()` – Filtra coleções já carregadas mapeando os estados e aplicando `trilean()->consensus()`.

### 4. 🌐 Request Macros (5 métodos)
- `ternary()` – Normaliza inputs diretamente do request.
- `hasTernaryTrue/False/Unknown()` – Ajudam a validar flags vindas de formulários.
- `ternaryGate()` – Executa portas AND/OR/consensus sobre múltiplos campos do request.
- `ternaryExpression()` – Avalia expressões DSL com o payload completo (`$request->all()`).

### 5. 🎨 Blade Directives (10+)
Diretivas registradas via `Blade::if` e `Blade::directive`, fornecendo sintaxe declarativa:
- `@ternary`, `@ternaryTrue/False/Unknown` – Condicionais sem precisar replicar helpers.
- `@maybe` – Renderiza saídas com `maybe()`.
- `@ternaryMatch` + `@case` – Pattern matching diretamente na view.
- `@ternaryBadge` / `@ternaryIcon` – Gera HTML pré-formatado com estilos dinâmicos.
- `@allTrue` / `@anyTrue` – Simplificam UI que depende de múltiplos checks.

### 6. 🛡️ Middleware
- `TernaryGateMiddleware` – Processa atributos de usuário/request, aplica operador escolhido e bloqueia com resposta JSON estruturada.
- `RequireTernaryTrue` – Garante rapidamente que um atributo (user ou request) esteja `TRUE`.

### 7. ✅ Validation Rules
Registradas via `Validator::extend`, permitem validações declarativas:
- `ternary`, `ternary_true`, `ternary_not_false` – Validações unitárias.
- `ternary_gate`, `ternary_any_true`, `ternary_all_true`, `ternary_consensus` – Validam múltiplos campos em conjunto.
- `ternary_weighted`, `ternary_expression` – Validam decisões baseadas em pesos ou expressões DSL.

### 8. 🧮 Recursos Avançados
- `TernaryArithmetic` – Soma/subtrai inteiros usando trits balanceados e carregamento inteligente.
- `CircuitBuilder` – Constroi DAGs de decisões com interface fluente, podendo exportar blueprints.
- Conversor BalancedTrit aprimorado – Suporta símbolos Unicode (`−`) e aliases (`POS`, `NEG`).

## Casos de Uso Detalhados
1. **Engine de Feature Flags**
   - Antes: múltiplos `if` com `cache`, `config`, `overrides`.
   - Com Trilean: helpers `maybe()` e `ternaryWeighted()` fornecem fallback elegante e logging simples.
2. **Workflows de Aprovação**
   - Antes: estados pendentes tratados como `false`.
   - Com Trilean: estados UNKNOWN permitem acompanhar progresso sem bloquear operações legítimas.
3. **Painel de Saúde**
   - Antes: booleanos que não distinguem degradação.
   - Com Trilean: `ternaryMajority()` e `ternaryScore()` expõem degradação vs. falha total.

## Sugestões Futuras (para encantar ainda mais)
- **Cache Ternário**: armazenar decisões com TTL por estado, evitando recomputações caras.
- **Observabilidade Ternária**: painel com timeline de estados TRUE/FALSE/UNKNOWN por recurso.
- **Policies Híbridas**: integração automática com `Gate::define` usando ternary fallback.
- **State Replay**: gravar `encodedVector` em auditorias e reproduzir decisões em lotes.
- **CLI Ternary Doctor**: comando Artisan que diagnostica rotas/requests e sugere otimizações.

---
Use este guia como referência rápida e compartilhe com o time para adoção imediata. O Trilean está pronto para elevar a produtividade e clareza das regras de negócio no Laravel.
