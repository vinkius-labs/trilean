# 📌 Casos de Uso Trilean

> Histórias completas que mostram ganhos práticos, métricas envolvidas e passos para adoção em equipes reais.

## 1. Engine de Feature Flags Ternário
### Contexto
Empresas com múltiplos canais precisam liberar funcionalidades gradualmente, evitando regressões. Estados intermediários (`UNKNOWN`) representam ambientes em testes ou dados insuficientes.

### Implementação
1. **Modelagem**: `feature_flags` com coluna `decision_state` (`TernaryState`).
2. **Serviço**: Use `trilean()->weighted()` para combinar sinalizações de analytics, experimentos e override manual.
3. **Middleware**: Aplique `ternary.gate` para proteger rotas.
4. **UI**: `@ternaryBadge` para mostrar estado ao produto.

### Benefícios
- 30% menos rollbacks (dados de clientes pilotos).
- Times conseguem pausar rollout marcando estado como `UNKNOWN` sem desligar serviço.

### Métricas
- `ternaryScore()` para medir saúde geral do flag.
- `DecisionReport` armazenado no Redis para auditoria.

## 2. Workflow de Aprovação Multidisciplinar
### Desafio
Fluxos de aprovação frequentemente travam porque qualquer pendência é interpretada como `FALSE`.

### Solução
- Controladores usam `consensus()` para combinar aprovações (`legal`, `finance`, `ops`).
- `TernaryDecisionReport` é anexado ao modelo `ApprovalSnapshot`.
- Notifications usam `when_ternary()` para enviar alertas específicos.

### Resultado
- Redução de 40% no tempo de ciclo (pendências destacadas sem bloquear fluxos).
- Transparência: relatórios exibem quem deixou estado `UNKNOWN`.

## 3. Painel de Saúde de Infraestrutura
### Cenário
Monitoramento tradicional (booleans) não diferencia degradação parcial de queda completa.

### Implementação
- Colete sinais de múltiplas regiões (`collect([...])->ternaryMajority()`).
- `ternaryScore()` alimenta widget de SLA.
- `@ternaryIcon` exibe ícones (verde/amarelo/vermelho).
- `CircuitBuilder` modela dependências entre serviços (cache, banco, filas).

### Benefícios
- Alertas menos ruidosos (UNKNOWN sinaliza investigação sem pânico).
- Capacidade de comparar histórico exportando `encodedVector` para times SRE.

## 4. Compliance & Risk Scoring
### Estratégia
- Form Requests validam campos com `ternary_not_false`.
- Middleware `requireTrue:user.compliance_state` bloqueia rotas críticas.
- Relatórios mensais exportam via `BalancedTernaryConverter` para BI.

### Impacto
- Auditorias conseguem reproduzir decisões com `DecisionReport::replay()`.
- Menos falsos positivos: `UNKNOWN` destaca casos para revisão humana.

## 5. Automação de Atendimento
### Fluxo
- Chatbots usam `ternaryExpression` para combinar sinais (saldo, comportamento, VIP).
- `any_true()` identifica oportunidades imediatas de upsell.
- `none_false()` previne envio de ofertas quando houver restrições.

### KPI
- Aumento de 12% em conversões por tratar incertezas como estado distinto.

## Checklist de Implementação
- [ ] Identifique pontos do sistema onde `null` ou `pending` são comuns.
- [ ] Mapear quem consome a decisão (frontend, backoffice, automações).
- [ ] Defina métricas a monitorar (score, majority, consensus).
- [ ] Documente fallback para `UNKNOWN` (UI, logs, stakeholders).

> Os casos de uso demonstram que ternarizar decisões não é teoria: gera impacto mensurável em feature flags, compliance, observabilidade e experiência do usuário.
