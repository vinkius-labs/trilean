# 📌 Casos de Uso Trilean

> Historias completas con impacto real, métricas y pasos de adopción.

## 1. Motor de Feature Flags Ternario
### Contexto
Empresas multicanal despliegan funcionalidades de forma gradual. `UNKNOWN` representa entornos en pruebas o datos incompletos.

### Implementación
1. Modelo `feature_flags` con columna `decision_state` (`TernaryState`).
2. Servicio: `trilean()->weighted()` mezcla analytics, experimentos y override manual.
3. Middleware: `ternary.gate` protege rutas críticas.
4. UI: `@ternaryBadge` comunica estado al equipo de producto.

### Beneficios
- 30% menos rollbacks en equipos piloto.
- Pausar despliegues cambiando a `UNKNOWN` sin apagar la feature.

### Métricas
- `ternaryScore()` alimenta dashboards.
- `TernaryDecisionReport` almacenado en Redis para auditorías.

## 2. Flujo de Aprobación Multidisciplinario
### Reto
Los workflows tradicionales tratan cualquier pendiente como `FALSE` y bloquean operaciones.

### Solución
- Controladores combinan aprobaciones (`legal`, `finance`, `ops`) con `consensus()`.
- Guardar `TernaryDecisionReport` en `ApprovalSnapshot`.
- Notificaciones utilizan `when_ternary()` para alertas específicas.

### Resultado
- 40% menos tiempo de ciclo.
- Transparencia total: reportes muestran quién dejó `UNKNOWN`.

## 3. Panel de Salud de Infraestructura
### Escenario
El monitoreo booleano no diferencia degradación parcial de caída total.

### Implementación
- Señales por región con `collect([...])->ternaryMajority()`.
- `ternaryScore()` alimenta SLA widgets.
- `@ternaryIcon` para UI consistente.
- `CircuitBuilder` modela dependencias (cache, DB, colas).

### Beneficios
- Alertas menos ruidosas.
- Equipos SRE comparan historiales `encodedVector` para detectar regresiones.

## 4. Compliance & Risk Scoring
- `FormRequest` valida con `ternary_not_false`.
- Middleware `ternary.requireTrue:user.compliance_state` protege rutas.
- Reportes mensuales exportan con `BalancedTernaryConverter` hacia BI.

### Impacto
- Auditorías reproducen decisiones con `DecisionReport::replay()`.
- Menos falsos positivos: `UNKNOWN` deriva a revisión manual.

## 5. Automatización de Atención
- Chatbots combinan señales con `ternaryExpression` (saldo, comportamiento, VIP).
- `any_true()` detecta oportunidades inmediatas.
- `none_false()` evita ofertas cuando hay restricciones.

### KPI
- +12% en conversiones al tratar la incertidumbre como estado explícito.

## Checklist de Implementación
- [ ] Identifica zonas con `null` o `pending` frecuentes.
- [ ] Mapea consumidores de la decisión (frontend, backoffice, automatizaciones).
- [ ] Define métricas a monitorear (`score`, `majority`, `consensus`).
- [ ] Documenta fallback para `UNKNOWN` (UI, logs, stakeholders).

> Ternarizar decisiones genera impacto medible en feature flags, compliance, observabilidad y experiencia del cliente.
