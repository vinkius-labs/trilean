# 🧮 Trilean Advanced Capabilities

> Power tools for complex scenarios: fluent API, decision builders, arithmetic, circuit orchestration, and balanced ternary conversions.

---

## 🆕 Fluent API

### Overview
Build complex ternary logic with chainable, readable syntax - an elegant alternative to nested ternary operators and if-else chains.

### Core Methods
| Method | Description |
| --- | --- |
| `ternary($value)` | Start fluent chain from any value |
| `->ifTrue($value)` | Set value to resolve when TRUE |
| `->ifFalse($value)` | Set value to resolve when FALSE |
| `->ifUnknown($value)` | Set value to resolve when UNKNOWN |
| `->whenTrue(callable)` | Execute callback when TRUE |
| `->whenFalse(callable)` | Execute callback when FALSE |
| `->whenUnknown(callable)` | Execute callback when UNKNOWN |
| `->resolve()` | Resolve to value based on state |
| `->execute()` | Execute callback and return result |
| `->pipe(callable)` | Transform resolved value |
| `->match(array)` | Pattern match with array |
| `->toBool()` | Convert to boolean (unknown = false) |
| `->state()` | Get underlying TernaryState |

### Examples

**Simple value resolution:**
```php
$status = ternary($subscription->active)
    ->ifTrue('premium')
    ->ifFalse('free')
    ->ifUnknown('trial')
    ->resolve();
```

**Callback execution:**
```php
ternary($user->verified)
    ->whenTrue(fn() => $this->grantAccess($user))
    ->whenFalse(fn() => $this->sendVerificationEmail($user))
    ->whenUnknown(fn() => $this->requestDocuments($user))
    ->execute();
```

**Chaining operations:**
```php
$formatted = ternary($payment->status)
    ->ifTrue('success')
    ->ifFalse('failed')
    ->ifUnknown('pending')
    ->pipe(fn($s) => strtoupper($s))
    ->pipe(fn($s) => "Payment: {$s}")
    ->resolve();
// Result: "Payment: SUCCESS"
```

**Pattern matching:**
```php
$badge = ternary($user->status)
    ->match([
        'true' => '<span class="badge-success">Active</span>',
        'false' => '<span class="badge-danger">Inactive</span>',
        'unknown' => '<span class="badge-warning">Pending</span>',
    ]);
```

### Use Cases
- **UI Status Rendering**: Convert ternary states to display values
- **Workflow Orchestration**: Execute different actions per state
- **Data Transformation**: Chain transformations on ternary values
- **Conditional Logic**: Replace nested ternary operators

---

## 🆕 Decision Builder (Fluent DSL)

### Overview
Build complex decision trees without verbose array blueprints - cleaner, more maintainable, and IDE-friendly.

### Core Methods
| Method | Description |
| --- | --- |
| `decide()` | Start new decision builder |
| `->input(name, value)` | Add input to decision tree |
| `->and(...inputs)` | Logical AND gate |
| `->or(...inputs)` | Logical OR gate |
| `->weighted(inputs, weights)` | Weighted decision |
| `->consensus(inputs, ratio)` | Consensus voting |
| `->requireAll(inputs)` | All must be TRUE |
| `->requireAny(inputs)` | At least one TRUE |
| `->toBool()` | Evaluate and convert to boolean |
| `->evaluate()` | Evaluate and return DecisionReport |

### Examples

**Simple AND logic:**
```php
$approved = decide()
    ->input('verified', $user->email_verified)
    ->input('consent', $user->gdpr_consent)
    ->and('verified', 'consent')
    ->toBool();
```

**Multi-level decision tree:**
```php
$canPurchase = decide()
    // Define inputs
    ->input('age_verified', $user->age >= 18)
    ->input('payment_valid', $payment->isValid())
    ->input('stock_available', $product->inStock())
    ->input('fraud_check', !$fraudDetector->isSuspicious())
    
    // Build decision gates
    ->and('age_verified', 'payment_valid')
    ->or('stock_available', 'fraud_check')
    ->requireAll(['age_verified', 'payment_valid'])
    ->toBool();
```

**Weighted approvals:**
```php
$approved = decide()
    ->input('legal', $approvals->legal)
    ->input('finance', $approvals->finance)
    ->input('executive', $approvals->executive)
    ->weighted(['legal', 'finance', 'executive'], [3, 2, 2])
    ->toBool();
```

**Full evaluation report:**
```php
$report = decide()
    ->input('check1', $validation1)
    ->input('check2', $validation2)
    ->input('check3', $validation3)
    ->consensus(['check1', 'check2', 'check3'], ratio: 0.66)
    ->evaluate();

$result = $report->result();        // TernaryState
$decisions = $report->decisions();  // Full audit trail
$vector = $report->encodedVector(); // "++0-" format
```

### Real-World Example: E-commerce Order Approval

```php
public function approveOrder(Order $order): OrderDecision
{
    $report = decide()
        // Customer validation
        ->input('customer_verified', $order->customer->isVerified())
        ->input('payment_valid', $order->payment->isValid())
        ->input('address_complete', $order->billingAddress->isComplete())
        
        // Inventory validation
        ->input('items_in_stock', $order->items->every->inStock())
        ->input('warehouse_ready', $this->warehouse->hasCapacity($order))
        
        // Risk assessment
        ->input('fraud_ok', $order->fraudScore < 50)
        ->input('velocity_ok', !$this->velocityChecker->isSuspicious($order))
        
        // Decision logic
        ->requireAll(['customer_verified', 'payment_valid', 'address_complete'])
        ->and('items_in_stock', 'warehouse_ready')
        ->consensus(['fraud_ok', 'velocity_ok'])
        ->evaluate();
    
    return new OrderDecision(
        approved: $report->result()->isTrue(),
        auditTrail: $report->decisions(),
        requiresReview: $report->result()->isUnknown(),
    );
}
```

### Performance: Memoization

Cache expensive decision evaluations:

```php
// In config/trilean.php
'cache' => [
    'enabled' => true,
    'ttl' => 3600,  // 1 hour
    'driver' => 'redis',
],

// In your code
$engine = app(TernaryDecisionEngine::class);

// First call - executes and caches
$report1 = $engine->memoize()->evaluate($blueprint);

// Second call - returns from cache
$report2 = $engine->memoize()->evaluate($blueprint);

// Clear cache when needed
$engine->clearCache();
```

### Migration from Array Blueprints

**Before (array blueprint):**
```php
$report = $engine->evaluate([
    'inputs' => [
        'verified' => $user->verified,
        'consent' => $user->consent,
    ],
    'gates' => [
        'final' => [
            'operator' => 'and',
            'operands' => ['verified', 'consent'],
        ],
    ],
    'output' => 'final',
]);
```

**After (fluent DSL):**
```php
$report = decide()
    ->input('verified', $user->verified)
    ->input('consent', $user->consent)
    ->and('verified', 'consent')
    ->evaluate();
```

**Benefits:**
- ✅ **50-70% less code**
- ✅ **IDE autocomplete support**
- ✅ **Type-safe method calls**
- ✅ **Easier to read and maintain**
- ✅ **No nested array structures**

---

## TernaryArithmetic
### Overview
Utility class for performing arithmetic on integers using balanced ternary (`-1`, `0`, `+1`). Ideal for finance, scoring, and symbolic AI algorithms.

### Key Methods
| Method | Description |
| --- | --- |
| `add(int|string $a, int|string $b)` | Adds balanced numbers (string or iterable input) |
| `subtract($a, $b)` | Subtraction with normalization |
| `toBalanced(int $value)` | Convert integer to balanced representation (array or string) |
| `fromBalanced(iterable|string $trits)` | Convert representation back to integer |
| `normalize(iterable $trits)` | Adjust carries into canonical form |

### Example
```php
$calc = new TernaryArithmetic();
$balanced = $calc->toBalanced(42); // e.g. ['+', '0', '-']
$result = $calc->add($balanced, '-+0');
```

### Use Cases
- Risk scoring (`high = +`, `low = -`).
- Translating legacy boolean algorithms while keeping precision.

## CircuitBuilder
### Purpose
Build ternary decision DAGs fluently—perfect for business pipelines and simulation environments.

### Core API
| Method | Role |
| --- | --- |
| `input(string $name, callable|mixed $source)` | Define inputs |
| `gate(string $name, string $operator, array $dependencies, array $options = [])` | Add gates |
| `emit(string $name, string $fromNode)` | Mark outputs |
| `report()` | Generate `TernaryDecisionReport` with timeline |
| `toGraphviz()` | Export graph for diagrams |

### Example
```php
$builder = CircuitBuilder::make()
    ->input('legal', fn () => ternary($doc->legal_state))
    ->input('finance', fn () => ternary($doc->finance_state))
    ->gate('compliance', 'consensus', ['legal', 'finance'], ['requiredRatio' => 0.6])
    ->emit('ready_to_publish', 'compliance');

$decision = $builder->resolve('ready_to_publish');
```

### Observability
- `report()` returns node history, weights used, execution time.
- `toBlueprint()` serializes circuits for persistence.

## Balanced Trit Converter
### Goal
Convert between balanced ternary representations (unicode `−`, ASCII `-`, aliases `POS/NEG/ZERO`).

### Features
- Accepts strings, arrays, and numbers.
- Configurable via `config('trilean.converters')`.
- Supports round-trip conversions (`encode` -> `decode`).

### Usage
```php
$converter = app(BalancedTernaryConverter::class);
$encoded = $converter->encode([-1, 0, 1]);
$decoded = $converter->decode('−0+');
```

## Integrating Features
- Combine `CircuitBuilder` + `TernaryArithmetic` to simulate financial impact under conditional decisions.
- Use the converter when exporting balanced data to legacy systems (e.g. `T`, `F`, `U`).

## Best Practices
- Document complex circuits via `->toGraphviz()` and attach diagrams to pull requests.
- Standardize export formats (ASCII vs Unicode) to avoid log drift.
- Cache `CircuitBuilder` results using `encodedVector` keys for heavy workloads.

> Advanced features push Trilean beyond simple `if` statements, enabling automation in compliance, finance, and AI-proofing domains.
