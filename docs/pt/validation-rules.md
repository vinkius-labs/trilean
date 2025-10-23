# ✅ Regras de Validação Ternárias

> Garanta integridade de entrada ao aceitar e combinar estados ternários em formulários, APIs e eventos.

## Visão Geral
As regras são registradas via `Validator::extend` no service provider, mapeando nomes amigáveis para closures que usam `TernaryLogicService` e os helpers. Elas podem ser usadas em `FormRequest`, validação manual (`Validator::make`) ou `Rule::` custom.

## Regras Disponíveis
| Regra | Tipo | Descrição |
| --- | --- | --- |
| `ternary` | Unitária | Campo deve ser convertível para `TernaryState` |
| `ternary_true` | Unitária | Campo deve resultar `TRUE` |
| `ternary_not_false` | Unitária | Campo não pode resultar `FALSE` |
| `ternary_gate` | Multi | Aplica operador (`and`, `or`, `xor`, `consensus`) |
| `ternary_any_true` | Multi | Ao menos um campo `TRUE` |
| `ternary_all_true` | Multi | Todos os campos `TRUE` |
| `ternary_consensus` | Multi | Votação customizada |
| `ternary_weighted` | Multi | Usa pesos e limiar configurável |
| `ternary_expression` | Multi | Avalia DSL ternária fornecida |

## Uso Básico
```php
$request->validate([
    'kyc_state' => ['required', 'ternary'],
    'aml_state' => ['required', 'ternary_not_false'],
]);
```

## Regras Multi-Campo
```php
$request->validate([
    'checks' => ['required', 'array'],
    'checks.*' => ['ternary'],
    'checks' => ['ternary_gate:and'],
]);
```
- **Sintaxe**: `ternary_gate:operator,options...`
- **Opções**: `requiredRatio=0.66`, `weights=legal:5,finance:3`, `report=true`.

### `ternary_expression`
```php
$request->validate([
    'decision' => ['required', 'ternary_expression:kyc && (aml || override)'],
]);
```
- A expressão é avaliada usando os campos do request (`kyc`, `aml`, `override`).
- Valores desconhecidos (`null`) geram `UNKNOWN` e podem invalidar conforme contexto.

## Customização de Mensagens
Adicione ao `lang/pt/validation.php`:
```php
'ternary' => 'O campo :attribute precisa ser verdadeiro, falso ou desconhecido.',
'ternary_gate' => 'A combinação ternária de :attribute não atingiu o limiar esperado.',
```

## Boas Práticas
- Sempre combine `ternary`/`ternary_not_false` para garantir inputs válidos antes de usar macros/helpers.
- Para regras multi-campo, declare `array` e `distinct` conforme necessidade.
- Em APIs públicas, exponha mensagens explicando o significado de `UNKNOWN` (documentação de contrato).

## Testes
- Utilize `Validator::make($data, ['state' => 'ternary_true'])->passes()`.
- Mocke pesos passando `ternary_weighted:weights=lead:5,auto:1,requiredRatio=0.7`.
- Para `ternary_expression`, cubra casos com `true`, `false`, `unknown` e strings customizadas.

## Observabilidade
- Regras `ternary_*` podem anexar `TernaryDecisionReport` ao validator (via `setData`) para auditoria.
- Logs centralizados: habilite `config('trilean.validation.log_failures')` para rastrear inputs inválidos.

> Validações ternárias são a primeira linha de defesa contra estados inválidos, garantindo que flows downstream operem com dados sem ambiguidade.
