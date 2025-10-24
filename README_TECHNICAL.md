# 🔧 Trilean Technical Reference

> **Complete feature documentation** for all Trilean capabilities. For performance metrics, see [README_PERFORMANCE.md](README_PERFORMANCE.md).

---

## Table of Contents

1. [Core Enum](#-core-enum)
2. [Global Helpers](#-global-helpers)
3. [Fluent API](#-fluent-api)
4. [Decision Engine](#-decision-engine)
5. [Domain Helpers](#-domain-helpers)
6. [Collection Macros](#-collection-macros)
7. [Request Macros](#-request-macros)
8. [Eloquent Macros](#️-eloquent-macros)
9. [Blade Directives](#-blade-directives)
10. [Validation Rules](#-validation-rules)
11. [Middleware](#️-middleware)
12. [Gate Integration](#-gate-integration)
13. [Traits](#-traits)
14. [Advanced Features](#-advanced-features)
15. [Console Commands](#-console-commands)

---

## 🎯 Core Enum

### `TernaryState` Enum

```php
use VinkiusLabs\Trilean\Enums\TernaryState;

enum TernaryState: string
{
    case TRUE = 'true';
    case FALSE = 'false';
    case UNKNOWN = 'unknown';
}
```

### Static Constructors

```php
// From any value
TernaryState::fromMixed(true);       // TRUE
TernaryState::fromMixed(false);      // FALSE
TernaryState::fromMixed(null);       // UNKNOWN
TernaryState::fromMixed(1);          // TRUE
TernaryState::fromMixed(0);          // FALSE
TernaryState::fromMixed('yes');      // TRUE
TernaryState::fromMixed('no');       // FALSE
TernaryState::fromMixed('unknown');  // UNKNOWN
```

### Instance Methods

```php
$state = TernaryState::fromMixed($value);

// State checks
$state->isTrue();      // bool
$state->isFalse();     // bool
$state->isUnknown();   // bool

// Transformations
$state->invert();      // Inverts TRUE <-> FALSE, UNKNOWN stays UNKNOWN
$state->label();       // Returns 'True', 'False', or 'Unknown'

// Conversions
$state->toInt();           // Returns 1, 0, or -1
$state->toNullableBool();  // Returns true, false, or null
$state->toBool(false);     // Convert UNKNOWN to boolean with default
```

### Fluent Chaining

```php
TernaryState::fromMixed($value)
    ->ifTrue('Activated')
    ->ifFalse('Deactivated')
    ->ifUnknown('Pending')
    ->resolve();  // Returns the matched value

TernaryState::fromMixed($value)
    ->whenTrue(fn() => Log::info('Success'))
    ->whenFalse(fn() => Log::error('Failed'))
    ->whenUnknown(fn() => Log::warning('Uncertain'))
    ->execute();  // Executes the matched callback

// Pattern matching
$state->match(
    ifTrue: 'Yes',
    ifFalse: 'No',
    ifUnknown: 'Maybe'
);

// Transformation pipeline
$state->pipe(fn($s) => $s->invert())
      ->pipe(fn($s) => $s->toBool());
```

---

## 🌍 Global Helpers

### Core Helpers

```php
// Convert to TernaryState
ternary($value);  // Returns TernaryState instance

// State checks (optimized fast paths)
is_true($value);     // bool
is_false($value);    // bool
is_unknown($value);  // bool
```

### Logic Operations

```php
// AND operation (all must be true)
and_all($verified, $consented, $active);  // bool
and_all(...$checks);  // Variadic

// OR operation (any must be true)
or_any($method1, $method2, $method3);  // bool
or_any(...$checks);  // Variadic
```

### Conditional Execution

```php
// Pick value based on condition
pick($condition, 'Yes', 'No', 'Maybe');  // Returns matched value

// Execute callbacks conditionally
when_true($verified, fn() => activateUser());
when_false($blocked, fn() => sendWelcome());
when_unknown($consent, fn() => requestConsent());
```

### Voting & Consensus

```php
// Simple majority vote
vote($check1, $check2, $check3);  // Returns 'true', 'false', or 'tie'
```

### Safe Conversions

```php
// Convert with explicit unknown handling
safe_bool($value, default: false);  // Returns boolean

// Coalesce with ternary awareness
ternary_coalesce($value, default: 'true', ifNull: 'unknown');
```

### Validation Helpers

```php
// Throw exception if not true
require_true($user->verified, 'User must be verified');

// Throw exception if false (allows true or unknown)
require_not_false($user->blocked, 'User is blocked');
```

### Array Helpers

```php
// Check all/any
array_all_true($values);  // bool
array_any_true($values);  // bool

// Filter arrays
array_filter_true($values);     // Keep only true values
array_filter_false($values);    // Keep only false values
array_filter_unknown($values);  // Keep only unknown values

// Count states
array_count_ternary($values);  // ['true' => 3, 'false' => 1, 'unknown' => 2]
```

### Pipeline Operations

```php
// Transform through pipeline
pipe_ternary($value, [
    fn($s) => $s->invert(),
    fn($s) => validateState($s),
    fn($s) => $s->toBool()
]);
```

### Pattern Matching

```php
// Wildcard matching
match_ternary($status, [
    'premium|enterprise' => 'Advanced',
    'free|trial' => 'Basic',
    '*' => 'Unknown'
]);

// Conditional matching
match_ternary($conditions, [
    [is_true($verified), is_true($active)] => 'Ready',
    [is_true($verified)] => 'Pending',
    'default' => 'Blocked'
]);
```

---

## 💎 Fluent API

### TernaryFluentBuilder

```php
ternary($value)
    ->ifTrue('Activated')
    ->ifFalse('Deactivated')
    ->ifUnknown('Pending')
    ->resolve();  // Get the matched value

ternary($value)
    ->whenTrue(fn() => activateUser())
    ->whenFalse(fn() => deactivateUser())
    ->whenUnknown(fn() => markPending())
    ->execute();  // Run the matched callback

// Get the state
$builder->state();  // Returns TernaryState

// Convert to boolean
$builder->toBool(unknownAs: false);

// Transform
$builder->pipe(fn($state) => $state->invert());
```

---

## 🧠 Decision Engine

### Decision Builder

```php
use function decide;

// Simple decision
$result = decide($user->verified, $user->consent)
    ->requireAll()
    ->toBool();

// Named inputs
$decision = decide()
    ->input('verified', $user->verified)
    ->input('consent', $user->consent)
    ->input('active', $user->active);

// Logic operations
$decision
    ->and('compliance', ['verified', 'consent'])
    ->or('access', ['compliance', 'admin_override'])
    ->requireTrue('access');

// Evaluate
$result = $decision->evaluate();  // Returns TernaryDecisionReport

// Get boolean result
$allowed = $decision->toBool();

// Custom logic
$decision->custom('risk_check', function($inputs) {
    return is_true($inputs['verified']) && !is_true($inputs['flagged']);
});
```

### TernaryDecisionReport

```php
$report = $decision->evaluate();

// Results
$report->result();        // TernaryState
$report->passed();        // bool
$report->failed();        // bool
$report->uncertain();     // bool

// Metadata
$report->inputs();        // Array of inputs
$report->outputs();       // Array of computed values
$report->blueprint();     // Decision logic structure
$report->auditTrail();    // Full audit log

// Export
$report->toArray();
$report->toJson();
```

---

## 🏢 Domain Helpers

### GDPR Compliance

```php
// Check if data processing is allowed
gdpr_can_process($consent, $legitimateInterest);  // bool

// Check if user action required
gdpr_requires_action($consent);  // bool

// Fluent API
use VinkiusLabs\Trilean\Support\Domain\GdprHelper;

(new GdprHelper($consent, $legitimateInterest))
    ->canProcess();       // bool
    ->requiresAction();   // bool
    ->needsConsent();     // bool
```

### Feature Flags

```php
// Check if enabled
feature($flag)->enabled();  // bool
feature($flag)->disabled(); // bool

// Rollout percentage
feature($flag)->rollout($user, percentage: 10);  // bool (10% of users)

// Environment-aware
feature($flag)->enabledInProduction();  // bool
```

### Risk Assessment

```php
// Calculate risk level
risk_level($signal1, $signal2, $signal3)
    ->acceptable();  // bool

risk_level(...$signals)
    ->threshold(0.7)   // Set acceptable threshold
    ->passed();        // bool

// Get score
$riskScore = risk_level(...$signals)->score();  // 0.0 - 1.0
```

### Fraud Detection

```php
// Fraud score calculation
fraud_score($check1, $check2, $check3)
    ->threshold(0.8)    // Set fraud threshold
    ->isSafe();         // bool

fraud_score(...$checks)
    ->score();          // 0.0 - 1.0 (higher = more fraud signals)
```

### Compliance Checking

```php
// Check compliance
compliant($legal, $finance, $security)->strict();  // bool (all must pass)
compliant(...$checks)->lenient();                  // bool (any can pass)

// Approval workflow
approved($dept1, $dept2, $dept3)->requireAll();   // bool
approved(...$approvals)->majority();               // bool
```

---

## 📦 Collection Macros

### Aggregation Macros

```php
use Illuminate\Support\Collection;

collect([true, 1, 'yes'])->allTrue();   // bool - check if all are true
collect([false, null, 0])->anyTrue();   // bool - check if any is true
```

### Filter Macros

```php
collect([true, false, null, 1, 0])
    ->onlyTrue();      // Keep only true values
    
collect($values)
    ->onlyFalse();     // Keep only false values
    
collect($values)
    ->onlyUnknown();   // Keep only unknown values
```

### Transformation Macros

```php
// Convert all to safe booleans
collect([true, null, 'yes'])
    ->toBooleans(defaultForUnknown: false);  // [true, false, true]
```

### Voting Macros

```php
// Simple majority
collect([$check1, $check2, $check3])
    ->vote();  // Returns 'true', 'false', or 'tie'
```

### Counting Macros

```php
collect($values)->countTrue();      // int
collect($values)->countFalse();     // int
collect($values)->countUnknown();   // int
```

---

## 🌐 Request Macros

```php
use Illuminate\Http\Request;

// Check request input states
$request->isTrue('verified');    // bool
$request->isFalse('blocked');    // bool
$request->isUnknown('consent');  // bool

// Pick value based on input
$request->pick('status', 'Active', 'Inactive', 'Pending');

// Validation
$request->requireTrue('terms', 'You must accept terms');
$request->requireNotFalse('consent', 'Consent denied');

// Multiple inputs
$request->allTrue(['verified', 'consent', 'active']);  // bool
$request->anyTrue(['method1', 'method2', 'method3']);  // bool

// Vote on multiple inputs
$request->vote(['check1', 'check2', 'check3']);  // 'true', 'false', or 'tie'
```

---

## 🗄️ Eloquent Macros

### Query Builder Macros

```php
use Illuminate\Database\Eloquent\Builder;

// Filter by ternary states
User::whereTernaryTrue('verified')->get();
User::whereTernaryFalse('blocked')->get();
User::whereTernaryUnknown('consent')->get();

// Require states
User::requireTernaryTrue('active')->first();
User::requireTernaryNotFalse('verified')->find($id);

// Count by states
User::countTernaryTrue('verified');    // int
User::countTernaryFalse('blocked');    // int
User::countTernaryUnknown('consent');  // int
```

---

## 🎨 Blade Directives

### State Directives

```blade
@true($user->verified)
    <span class="badge badge-success">Verified</span>
@endtrue

@false($user->blocked)
    <span class="badge badge-danger">Blocked</span>
@endfalse

@unknown($user->consent)
    <span class="badge badge-warning">Pending Consent</span>
@endunknown
```

### Inline Pick Directive

```blade
<span class="status">
    @pick($user->active, 'Active', 'Inactive', 'Pending')
</span>
```

### Logic Directives

```blade
@all($user->verified, $user->consent, $user->active)
    <div class="alert alert-success">All requirements met</div>
@endall

@any($payment->method1, $payment->method2, $payment->method3)
    <div class="alert alert-info">At least one payment method available</div>
@endany
```

### Vote Directive

```blade
<div class="decision">
    @vote($check1, $check2, $check3)
</div>
<!-- Outputs: 'true', 'false', or 'tie' -->
```

### Safe Directive

```blade
<input 
    type="checkbox" 
    @safe($user->settings, 'checked', '')
/>
<!-- Converts unknown to safe boolean with default -->
```

---

## ✅ Validation Rules

### Available Rules

```php
use Illuminate\Support\Facades\Validator;

// Value must be true
$validator = Validator::make($data, [
    'terms' => 'must_be_true',
]);

// Value cannot be false (allows true or unknown)
$validator = Validator::make($data, [
    'consent' => 'cannot_be_false',
]);

// Value must be known (not unknown)
$validator = Validator::make($data, [
    'verified' => 'must_be_known',
]);

// All array values must be true
$validator = Validator::make($data, [
    'permissions' => 'all_must_be_true',
]);

// At least one array value must be true
$validator = Validator::make($data, [
    'methods' => 'any_must_be_true',
]);

// Majority of array values must be true
$validator = Validator::make($data, [
    'approvals' => 'majority_true',
]);

// Conditional validation
$validator = Validator::make($data, [
    'consent' => 'true_if:marketing_enabled,true',
]);

$validator = Validator::make($data, [
    'blocked' => 'false_if:verified,true',
]);
```

### Custom Messages

```php
$messages = [
    'terms.must_be_true' => 'You must accept the terms and conditions.',
    'consent.cannot_be_false' => 'Consent cannot be denied.',
    'verified.must_be_known' => 'Verification status must be determined.',
];

$validator = Validator::make($data, $rules, $messages);
```

---

## 🛡️ Middleware

### RequireTernaryTrue

```php
// In routes/web.php
Route::middleware('ternary.true:verified,consent')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Register in app/Http/Kernel.php
protected $middlewareAliases = [
    'ternary.true' => \VinkiusLabs\Trilean\Http\Middleware\RequireTernaryTrue::class,
];
```

### TernaryGateMiddleware

```php
// Check gate abilities with ternary support
Route::middleware('ternary.gate:edit-post,{post}')->group(function () {
    Route::put('/posts/{post}', [PostController::class, 'update']);
});

protected $middlewareAliases = [
    'ternary.gate' => \VinkiusLabs\Trilean\Http\Middleware\TernaryGateMiddleware::class,
];
```

---

## 🚪 Gate Integration

### Define Ternary Gates

```php
use Illuminate\Support\Facades\Gate;

// Define gate with ternary support
Gate::defineTernary('edit-post', function ($user, $post) {
    return ternary($user->isAdmin())
        ->orWhen($post->user_id === $user->id && is_true($user->verified));
});

// Inspect ternary gate
$inspection = Gate::inspectTernary('edit-post', [$post]);
// Returns: ['state' => TernaryState, 'passed' => bool, 'context' => [...]]
```

### MacroableGate

```php
use VinkiusLabs\Trilean\Support\GateMacros;

// Register macros
GateMacros::register();

// Use ternary gates
Gate::defineTernary('approve-order', function ($user, $order) {
    return and_all(
        $user->hasPermission('approve'),
        !is_true($order->is_flagged),
        is_true($order->is_verified)
    );
});
```

---

## 🧩 Traits

### HasTernaryState

```php
use VinkiusLabs\Trilean\Traits\HasTernaryState;

class User extends Model
{
    use HasTernaryState;
    
    protected $ternaryField = 'verified';  // Default field to use
}

// Usage
$state = $user->getTernaryState();  // TernaryState
$user->setTernaryState(TernaryState::TRUE);

$user->isTernaryTrue();      // bool
$user->isTernaryFalse();     // bool
$user->isTernaryUnknown();   // bool

$label = $user->ternaryStateLabel();  // 'True', 'False', or 'Unknown'
```

---

## 🚀 Advanced Features

### Balanced Ternary Arithmetic

```php
use VinkiusLabs\Trilean\Support\TernaryArithmetic;
use VinkiusLabs\Trilean\Support\BalancedTernaryConverter;

$converter = new BalancedTernaryConverter();
$arithmetic = new TernaryArithmetic($converter);

// Add in balanced ternary
$result = $arithmetic->add(5, 3);  // 8

// Subtract in balanced ternary
$result = $arithmetic->subtract(10, 4);  // 6
```

### Ternary Vector Operations

```php
use VinkiusLabs\Trilean\Collections\TernaryVector;

$vector = new TernaryVector([true, false, null, 1, 0]);

// Get by state
$vector->true();      // [0 => true, 3 => 1]
$vector->false();     // [1 => false, 4 => 0]
$vector->unknown();   // [2 => null]

// Count by state
$vector->countTrue();     // 2
$vector->countFalse();    // 2
$vector->countUnknown();  // 1

// All/Any checks
$vector->allTrue();   // bool
$vector->anyTrue();   // bool

// Invert
$inverted = $vector->invert();  // Flips TRUE <-> FALSE, UNKNOWN stays
```

### Pattern Matching

```php
use VinkiusLabs\Trilean\Support\TernaryPatternMatcher;

// Wildcard matching
TernaryPatternMatcher::matchWildcard($status, [
    'premium|enterprise' => 'Advanced',
    'free|trial' => 'Basic',
    '*' => 'Unknown'
]);

// Condition matching
TernaryPatternMatcher::matchConditions($conditions, [
    [is_true($verified), is_true($active)] => 'Ready',
    [is_true($verified)] => 'Pending',
    'default' => 'Blocked'
]);
```

### Metrics & Monitoring

```php
use VinkiusLabs\Trilean\Support\Metrics\TernaryMetrics;
use VinkiusLabs\Trilean\Events\TernaryDecisionEvaluated;

// Boot metrics collection
TernaryMetrics::boot();

// Record state changes
TernaryMetrics::recordState($state, ['context' => 'user_verification']);

// Record decisions
TernaryMetrics::recordDecision($report, $context, $blueprint);

// Listen to decision events
Event::listen(TernaryDecisionEvaluated::class, function ($event) {
    Log::info('Decision evaluated', [
        'result' => $event->result,
        'duration' => $event->duration,
        'inputs' => $event->inputs
    ]);
});
```

### Form Request Integration

```php
use VinkiusLabs\Trilean\Support\FormRequests\TernaryFormRequest;

class UpdateUserRequest extends TernaryFormRequest
{
    public function rules()
    {
        return [
            'verified' => 'must_be_true',
            'consent' => 'cannot_be_false',
            'active' => 'must_be_known',
        ];
    }
    
    public function ternaryRules()
    {
        return [
            'all_permissions' => 'all_must_be_true',
            'any_payment' => 'any_must_be_true',
        ];
    }
}
```

---

## 🔧 Console Commands

### Install Command

```bash
# Publish config, stubs, and resources
php artisan trilean:install

# Options
php artisan trilean:install --force  # Overwrite existing files
```

This publishes:
- `config/trilean.php` - Configuration file
- `resources/stubs/` - Code generation stubs
- Blade components (optional)

### Doctor Command

```bash
# Check Trilean health and configuration
php artisan trilean:doctor

# Options
php artisan trilean:doctor --verbose  # Detailed output
```

Checks:
- Package installation
- Configuration validity
- Registered macros and directives
- Performance optimization status
- Database compatibility
- Integration status (Telescope, Horizon, etc.)

---

## ⚙️ Configuration

### config/trilean.php

```php
return [
    // Default behavior for UNKNOWN state
    'unknown_as' => false,  // Boolean value for unknown when converted
    
    // Strict mode (throw exceptions on invalid conversions)
    'strict' => false,
    
    // Enable metrics collection
    'metrics' => [
        'enabled' => true,
        'drivers' => [
            'telescope' => ['enabled' => true],
            'redis' => ['enabled' => false, 'connection' => 'default'],
        ],
    ],
    
    // Gate integration
    'gate' => [
        'ternary_support' => true,
        'default_unknown' => false,
    ],
    
    // Validation
    'validation' => [
        'register_rules' => true,
        'custom_messages' => [],
    ],
    
    // Performance
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,  // seconds
        'driver' => 'redis',
    ],
];
```

---

## 📚 Type Conversion Reference

### Input Types Supported

| Input Type | TRUE | FALSE | UNKNOWN |
|------------|------|-------|---------|
| **Boolean** | `true` | `false` | N/A |
| **Integer** | `1` | `0` | `-1` |
| **String** | `'true'`, `'yes'`, `'1'`, `'on'`, `'enabled'` | `'false'`, `'no'`, `'0'`, `'off'`, `'disabled'` | `'unknown'`, `'null'`, `'maybe'`, `'-1'` |
| **Null** | N/A | N/A | `null` |
| **Array** | `['state' => 'true']` | `['state' => 'false']` | `['state' => 'unknown']` |
| **Object** | Objects with `__toString()` returning truthy values | Objects with `__toString()` returning falsy values | Objects with `__toString()` returning 'unknown' |

### String Matching (Case-Insensitive)

**TRUE values:**
- `'true'`, `'True'`, `'TRUE'`
- `'yes'`, `'Yes'`, `'YES'`
- `'1'`, `'on'`, `'enabled'`, `'active'`

**FALSE values:**
- `'false'`, `'False'`, `'FALSE'`
- `'no'`, `'No'`, `'NO'`
- `'0'`, `'off'`, `'disabled'`, `'inactive'`

**UNKNOWN values:**
- `'unknown'`, `'Unknown'`, `'UNKNOWN'`
- `'null'`, `'NULL'`, `'maybe'`, `'pending'`
- `'-1'`, `'uncertain'`

---

## 🎓 Best Practices

### 1. Use Specific Helpers for Common Cases

```php
// ✅ GOOD: Use specific helper
if (is_true($user->verified)) {
    // ...
}

// ❌ AVOID: Verbose conversion
if (ternary($user->verified)->isTrue()) {
    // ...
}
```

### 2. Leverage Early Returns in Logic

```php
// ✅ GOOD: Optimized with early returns
if (!is_true($user->verified)) return false;
if (!is_true($user->consented)) return false;
if (!is_true($user->active)) return false;
return true;

// ⚠️ SLOWER: Processes all values
return and_all($user->verified, $user->consented, $user->active);
```

### 3. Convert Once, Reuse Many Times

```php
// ✅ GOOD: Convert once
$verified = ternary($user->verified);
if ($verified->isTrue()) { /* ... */ }
if ($verified->isTrue()) { /* ... */ }

// ❌ AVOID: Multiple conversions
if (is_true($user->verified)) { /* ... */ }
if (is_true($user->verified)) { /* ... */ }
```

### 4. Use Collections for Batch Operations

```php
// ✅ GOOD: Collection macros
collect($permissions)->allTrue();

// ⚠️ SLOWER: Manual iteration
array_all_true($permissions);
```

### 5. Explicit Unknown Handling

```php
// ✅ GOOD: Explicit handling
$active = safe_bool($user->active, default: false);

// ❌ RISKY: Implicit conversion
$active = (bool) $user->active;  // Null becomes false unexpectedly
```

---

## 🐛 Troubleshooting

### Common Issues

**Issue:** "Class 'TernaryState' not found"
```php
// Solution: Use full namespace
use VinkiusLabs\Trilean\Enums\TernaryState;
```

**Issue:** Macros not working
```bash
# Solution: Clear cache
php artisan optimize:clear
php artisan config:clear
```

**Issue:** Blade directives not registered
```php
// Solution: Check service provider is loaded
php artisan trilean:doctor
```

**Issue:** Performance degradation in tight loops
```php
// Solution: Use native PHP for 10M+ iterations
foreach ($hugeArray as $value) {
    if ($value === true) {  // Native check
        $count++;
    }
}
```

---

## 📖 Additional Resources

- **Performance Guide:** [README_PERFORMANCE.md](README_PERFORMANCE.md)
- **Main Documentation:** [README.md](README.md)
- **Examples:** `docs/` directory
- **Use Cases:** [docs/en/use-cases.md](docs/en/use-cases.md)
- **Future Ideas:** [docs/en/future-ideas.md](docs/en/future-ideas.md)

---