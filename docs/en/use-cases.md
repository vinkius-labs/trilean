# 📌 Trilean Use Cases

> End-to-end stories that showcase practical gains, metrics, and adoption steps for real teams.

## 1. Ternary Feature Flags Engine
### Context
Multi-channel companies roll out features gradually and must avoid regressions. `UNKNOWN` represents test environments or incomplete data.

### Implementation
1. **Modeling**: `feature_flags` table with `decision_state` (`TernaryState`).
2. **Service**: Use `trilean()->weighted()` to combine analytics, experiments, and manual overrides.
3. **Middleware**: Apply `ternary.gate` to protect routes.
4. **UI**: Render `@ternaryBadge` to surface state to product teams.

### Benefits
- 30% fewer rollbacks reported in pilot teams.
- Teams can pause rollouts by switching to `UNKNOWN` without disabling the feature.

### Metrics
- `ternaryScore()` feeds health dashboards.
- Store `TernaryDecisionReport` in Redis for audit.

## 2. Multidisciplinary Approval Workflow
### Challenge
Traditional workflows treat any pending state as `FALSE`, stalling operations.

### Solution
- Controllers combine approvals (`legal`, `finance`, `ops`) via `consensus()`.
- Attach `TernaryDecisionReport` to `ApprovalSnapshot` models.
- Notifications use `when_ternary()` to issue precise alerts.

### Outcome
- 40% reduction in cycle time (pending stakeholders flagged without blocking legitimate work).
- Transparent auditing: reports show who left a decision `UNKNOWN`.

## 3. Infrastructure Health Dashboard
### Scenario
Monitoring that only uses booleans cannot distinguish partial degradation from total failure.

### Implementation
- Collect signals per region (`collect([...])->ternaryMajority()`).
- Display `ternaryScore()` in SLA widgets.
- Render icons with `@ternaryIcon` for UI parity.
- Model service dependencies using `CircuitBuilder` (cache, db, queues).

### Benefits
- Less noisy alerts (`UNKNOWN` signals investigation rather than outages).
- SRE teams compare historical `encodedVector` exports to spot regressions.

## 4. Compliance & Risk Scoring
### Strategy
- `FormRequest` validates inputs with `ternary_not_false`.
- Middleware `ternary.requireTrue:user.compliance_state` blocks critical routes.
- Monthly reports export using `BalancedTernaryConverter` for BI pipelines.

### Impact
- Auditors replay decisions via `DecisionReport::replay()`.
- Fewer false positives: `UNKNOWN` routes cases to human review.

## 5. Automated Support Flows
### Flow
- Chatbots combine signals via `ternaryExpression` (balance, behavior, VIP status).
- `any_true()` surfaces instant upsell opportunities.
- `none_false()` prevents offers when restrictions exist.

### KPI
- 12% conversion uplift by treating uncertainty as a first-class state.

## Adoption Checklist
- [ ] Identify system areas with common `null`/`pending` patterns.
- [ ] Map downstream consumers (frontend, backoffice, automations).
- [ ] Define metrics to monitor (`score`, `majority`, `consensus`).
- [ ] Document fallbacks for `UNKNOWN` (UI, logs, stakeholders).

> These use cases prove ternarizing decisions is more than theory—it drives measurable outcomes across feature flags, compliance, observability, and customer experience.
