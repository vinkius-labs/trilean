# 📘 Guia Trilean em Português

## 🎯 Visão Geral
Trilean traz **computação ternária** para Laravel. Cada decisão abraça `TRUE`, `FALSE` e `UNKNOWN`, eliminando surpresas causadas por incompatibilidades de valores nulos/fonte da verdade.

**Por que Trilean?**
- 🔒 **Type-safe** lógica de três estados (sem mais bugs de `null`)
- 🚀 **Zero boilerplate** com helpers globais e macros
- 🎨 **Expressivo** diretivas Blade e regras de validação
- 📊 **Observabilidade** com métricas e rastreamento de decisões integrados
- 🧮 **Avançado** consenso, votação ponderada e aritmética balanceada

---

## 🔄 Antes vs Depois

### Cenário 1: Onboarding de Usuário
**❌ Antes (caos booleano)**
```php
// Bugs ocultos: e se verified for NULL?
if ($user->verified && $user->email_confirmed && $user->terms_accepted) {
    $user->activate();
    return redirect('/dashboard');
}

// Sem visibilidade do PORQUÊ eles não podem prosseguir
return back()->with('error', 'Não é possível ativar a conta');
```

**✅ Depois (clareza Trilean)**
```php
if (all_true($user->verified, $user->email_confirmed, $user->terms_accepted)) {
    $user->activate();
    return redirect('/dashboard');
}

// Tratamento explícito de cada estado
return maybe(
    consensus($user->verified, $user->email_confirmed, $user->terms_accepted),
    ifTrue: fn() => redirect('/dashboard'),
    ifFalse: fn() => back()->with('error', 'Requisitos não atendidos'),
    ifUnknown: fn() => redirect('/verificacao-pendente')
);
```

### Cenário 2: Feature Flags com Rollout
**❌ Antes (condicionais complexas)**
```php
$podeAcessarBeta = false;

if ($user->is_beta_tester) {
    $podeAcessarBeta = true;
} elseif ($user->plan === 'enterprise' && $feature->rollout_percent > 50) {
    $podeAcessarBeta = rand(1, 100) <= $feature->rollout_percent;
} elseif ($feature->enabled === null) {
    // O que null significa? Estado desconhecido leva a bugs
    $podeAcessarBeta = false;
}

if ($podeAcessarBeta) {
    return view('beta.dashboard');
} else {
    return view('standard.dashboard');
}
```

**✅ Depois (motor de decisão Trilean)**
```php
$estado = ternary_match(
    consensus(
        $user->is_beta_tester,
        $user->plan === 'enterprise' && $feature->rollout_percent > 50,
        $feature->enabled
    ),
    [
        'true' => 'concedido',
        'false' => 'negado',
        'unknown' => 'aguardando_rollout'
    ]
);

return when_ternary(
    $estado,
    onTrue: fn() => view('beta.dashboard'),
    onFalse: fn() => view('standard.dashboard'),
    onUnknown: fn() => view('pending.dashboard')
);
```

### Cenário 3: Fluxo de Aprovação
**❌ Antes (condicionais aninhadas)**
```php
if (!$doc->legal_approved) {
    return ['status' => 'pendente', 'motivo' => 'revisão jurídica'];
}

if (!$doc->finance_approved) {
    return ['status' => 'pendente', 'motivo' => 'revisão financeira'];
}

if (!$doc->manager_approved) {
    return ['status' => 'pendente', 'motivo' => 'aprovação do gerente'];
}

// Todos aprovados - mas e se um for null?
return ['status' => 'publicado'];
```

**✅ Depois (consenso ponderado Trilean)**
```php
$estado = collect([
    'juridico' => $doc->legal_approved,
    'financeiro' => $doc->finance_approved,
    'gerente' => $doc->manager_approved,
])->ternaryWeighted([5, 3, 2]); // Jurídico tem mais peso

return ternary_match($estado, [
    'true' => ['status' => 'publicado', 'aprovado_por' => 'todos'],
    'false' => ['status' => 'rejeitado', 'motivo' => 'falha_aprovacao'],
    'unknown' => ['status' => 'em_revisao', 'departamentos_pendentes' => $this->getDepartamentosPendentes()],
]);
```

---

## 📚 Recursos Técnicos

Veja a documentação detalhada em inglês para exemplos completos de cada recurso: [English Guide](./ternary-guide.en.md)

### 1. 🔥 Helpers Globais (10 funções)
- `ternary()` - Conversão inteligente para TernaryState
- `maybe()` - Ramificação em três vias
- `trilean()` - Acesso ao serviço principal
- `ternary_vector()` - Operações matemáticas em coleções
- `all_true()` / `any_true()` - Portas lógicas
- `none_false()` - Garantir ausência de FALSE
- `consensus()` - Decisões democráticas
- `when_ternary()` - Execução condicional
- `ternary_match()` - Pattern matching

### 2. 💎 Macros de Collection (12 métodos)
- `ternaryConsensus()` / `ternaryMajority()`
- `whereTernaryTrue/False/Unknown()`
- `ternaryWeighted()` - Votação ponderada
- `ternaryMap()` - Mapeamento ternário
- `ternaryScore()` - Pontuação balanceada
- `allTernaryTrue()` / `anyTernaryTrue()`
- `partitionTernary()` - Divisão em três grupos
- `ternaryGate()` - Portas lógicas flexíveis

### 3. 🗄️ Scopes Eloquent (8 métodos)
- `whereTernaryTrue/False/Unknown()`
- `orderByTernary()` - Ordenação inteligente
- `whereAllTernaryTrue()` / `whereAnyTernaryTrue()`
- `ternaryConsensus()`

### 4. 🌐 Macros de Request (5 métodos)
- `ternary()` - Normalização de input
- `hasTernaryTrue/False/Unknown()`
- `ternaryGate()` - Validação multi-campo
- `ternaryExpression()`

### 5. 🎨 Diretivas Blade (10+)
- `@ternaryTrue/False/Unknown`
- `@ternaryMatch` - Pattern matching em templates
- `@allTrue` / `@anyTrue`
- `@ternaryBadge` / `@ternaryIcon`

### 6. 🛡️ Middleware
- `TernaryGateMiddleware` - Proteção de rotas com lógica ternária

### 7. ✅ Regras de Validação
- Básicas: `ternary`, `ternary_true`, `ternary_not_false`
- Avançadas: `ternary_gate`, `ternary_consensus`, `ternary_weighted`

### 8. 🧮 Recursos Avançados
- Decision Engine com blueprints
- Aritmética ternária balanceada
- Circuit Builder

---

## 📖 Documentação Detalhada

- **[Helpers Globais](./pt/helpers-globais.md)** - Todas as 10 funções helper com exemplos
- **[Macros de Collection](./pt/collection-macros.md)** - 12 métodos Collection para lógica ternária
- **[Scopes Eloquent](./pt/eloquent-scopes.md)** - Queries de banco de dados com estados ternários
- **[Macros de Request](./pt/request-macros.md)** - Tratamento ternário de requisições HTTP
- **[Diretivas Blade](./pt/blade-directives.md)** - Diretivas de template para views
- **[Regras de Validação](./pt/validation-rules.md)** - Validação de formulários com lógica ternária
- **[Middleware](./pt/middleware.md)** - Proteção de rotas com gates ternários
- **[Recursos Avançados](./pt/recursos-avancados.md)** - Decision Engine, Aritmética, Circuitos
- **[Casos de Uso](./pt/casos-de-uso.md)** - Padrões de implementação do mundo real

---

## 🚀 Instalação

```bash
composer require vinkius-labs/trilean
```

### Publicar Configuração
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

## 📄 Licença

Licença MIT - veja o arquivo [LICENSE](../LICENSE) para detalhes.

---

**Construído com ❤️ por VinkiusLabs** | [GitHub](https://github.com/vinkius-labs/trilean) | [Issues](https://github.com/vinkius-labs/trilean/issues)

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
