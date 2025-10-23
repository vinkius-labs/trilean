# 📘 Trilean Guide (English)

## 🎯 Overview
Trilean brings **ternary computing** to Laravel. Every decision embraces `TRUE`, `FALSE`, and `UNKNOWN`, eliminating surprises caused by nullable/source-of-truth mismatches.

**Why Trilean?**
- 🔒 **Type-safe** three-state logic (no more `null` bugs)
- 🚀 **Zero boilerplate** with global helpers and macros
- 🎨 **Expressive** Blade directives and validation rules
- 📊 **Observability** with built-in metrics and decision tracking
- 🧮 **Advanced** consensus, weighted voting, and balanced arithmetic

---

## 🔄 Before vs After

### Scenario 1: User Onboarding
**❌ Before (boolean chaos)**
```php
// Hidden bugs: what if verified is NULL?
if ($user->verified && $user->email_confirmed && $user->terms_accepted) {
    $user->activate();
    return redirect('/dashboard');
}

// No visibility into WHY they can't proceed
return back()->with('error', 'Cannot activate account');
```

**✅ After (Trilean clarity)**
```php
if (all_true($user->verified, $user->email_confirmed, $user->terms_accepted)) {
    $user->activate();
    return redirect('/dashboard');
}

// Explicit handling of each state
return maybe(
    consensus($user->verified, $user->email_confirmed, $user->terms_accepted),
    ifTrue: fn() => redirect('/dashboard'),
    ifFalse: fn() => back()->with('error', 'Requirements not met'),
    ifUnknown: fn() => redirect('/pending-verification')
);
```

### Scenario 2: Feature Flags with Rollout
**❌ Before (complex conditionals)**
```php
$canAccessBeta = false;

if ($user->is_beta_tester) {
    $canAccessBeta = true;
} elseif ($user->plan === 'enterprise' && $feature->rollout_percent > 50) {
    $canAccessBeta = rand(1, 100) <= $feature->rollout_percent;
} elseif ($feature->enabled === null) {
    // What does null mean? Unknown state leads to bugs
    $canAccessBeta = false;
}

if ($canAccessBeta) {
    return view('beta.dashboard');
} else {
    return view('standard.dashboard');
}
```

**✅ After (Trilean decision engine)**
```php
$state = ternary_match(
    consensus(
        $user->is_beta_tester,
        $user->plan === 'enterprise' && $feature->rollout_percent > 50,
        $feature->enabled
    ),
    [
        'true' => 'granted',
        'false' => 'denied',
        'unknown' => 'awaiting_rollout'
    ]
);

return when_ternary(
    $state,
    onTrue: fn() => view('beta.dashboard'),
    onFalse: fn() => view('standard.dashboard'),
    onUnknown: fn() => view('pending.dashboard')
);
```

### Scenario 3: Approval Workflow
**❌ Before (nested conditionals)**
```php
if (!$doc->legal_approved) {
    return ['status' => 'pending', 'reason' => 'legal review'];
}

if (!$doc->finance_approved) {
    return ['status' => 'pending', 'reason' => 'finance review'];
}

if (!$doc->manager_approved) {
    return ['status' => 'pending', 'reason' => 'manager approval'];
}

// All approved - but what if one is null?
return ['status' => 'published'];
```

**✅ After (Trilean weighted consensus)**
```php
$state = collect([
    'legal' => $doc->legal_approved,
    'finance' => $doc->finance_approved,
    'manager' => $doc->manager_approved,
])->ternaryWeighted([5, 3, 2]); // Legal has most weight

return ternary_match($state, [
    'true' => ['status' => 'published', 'approved_by' => 'all'],
    'false' => ['status' => 'rejected', 'reason' => 'failed_approval'],
    'unknown' => ['status' => 'in_review', 'pending_departments' => $this->getPendingDepartments()],
]);
```

### Scenario 4: Health Checks
**❌ Before (confusing aggregation)**
```php
$services = [
    'database' => $this->checkDatabase(),
    'cache' => $this->checkCache(),
    'queue' => $this->checkQueue(),
];

$allHealthy = true;
foreach ($services as $status) {
    if ($status !== true) {
        $allHealthy = false;
        break;
    }
}

return response()->json([
    'healthy' => $allHealthy,
    'services' => $services
]);
```

**✅ After (Trilean consensus with scoring)**
```php
$vector = ternary_vector([
    'database' => $this->checkDatabase(),
    'cache' => $this->checkCache(),
    'queue' => $this->checkQueue(),
]);

return response()->json([
    'status' => $vector->majority()->label(), // 'true', 'false', 'unknown'
    'score' => $vector->score(), // +3 = all healthy, -3 = all down, 0 = mixed
    'consensus' => $vector->consensus()->value,
    'services' => $vector->partition()
]);
```

---

## 📚 Technical Highlights

### 1. 🔥 Global Helpers (10 functions)

#### `ternary()` - Smart Conversion
**What it does**: Converts any value to `TernaryState` enum (`TRUE`, `FALSE`, `UNKNOWN`)

**❌ Before**
```php
// Unsafe type juggling
$verified = $user->verified ?? false;
if ($verified) {
    // What if verified was null? We'd treat it as false!
}
```

**✅ After**
```php
$state = ternary($user->verified);

if ($state->isTrue()) {
    // Explicit TRUE handling
} elseif ($state->isUnknown()) {
    // Handle null/unknown explicitly
}
```

#### `maybe()` - Three-Way Branching
**What it does**: Execute different callbacks based on ternary state

**❌ Before**
```php
if ($feature->enabled === true) {
    return $this->enablePremium();
} elseif ($feature->enabled === false) {
    Log::info('Feature disabled');
    return null;
} else {
    return $this->queueReview();
}
```

**✅ After**
```php
return maybe($feature->enabled,
    ifTrue: fn() => $this->enablePremium(),
    ifFalse: fn() => Log::info('Feature disabled'),
    ifUnknown: fn() => $this->queueReview()
);
```

#### `all_true()` / `any_true()` - Logic Gates
**❌ Before**
```php
// Hard to read, error-prone
if ($user->verified && $user->active && $user->consented && !$user->blocked) {
    // proceed
}

// What about nulls?
if ($method1 || $method2 || $method3) {
    // Which one was true?
}
```

**✅ After**
```php
if (all_true($user->verified, $user->active, $user->consented, !$user->blocked)) {
    // Clean AND gate with ternary awareness
}

if (any_true($method1, $method2, $method3)) {
    // Clean OR gate - returns true if any is TRUE
}
```

#### `consensus()` - Democratic Decisions
**❌ Before**
```php
$votes = [$approver1, $approver2, $approver3, $approver4];
$yes = count(array_filter($votes, fn($v) => $v === true));
$no = count(array_filter($votes, fn($v) => $v === false));

if ($yes > $no) {
    return 'approved';
} else {
    return 'rejected';
}
```

**✅ After**
```php
$decision = consensus($approver1, $approver2, $approver3, $approver4);

return $decision->label(); // 'true', 'false', or 'unknown' based on majority
```

#### `ternary_match()` - Pattern Matching
**❌ Before**
```php
$status = ternary($order->approved);

if ($status->isTrue()) {
    return 'Approved';
} elseif ($status->isFalse()) {
    return 'Rejected';
} else {
    return 'Pending';
}
```

**✅ After**
```php
return ternary_match($order->approved, [
    'true' => 'Approved',
    'false' => 'Rejected',
    'unknown' => 'Pending'
]);
```

---

### 2. 💎 Collection Macros (12 methods)

#### `ternaryConsensus()` / `ternaryMajority()`
**❌ Before**
```php
$healthChecks = collect([$db, $cache, $queue, $redis]);
$healthy = $healthChecks->filter(fn($v) => $v === true)->count();
$total = $healthChecks->count();

if ($healthy > $total / 2) {
    return 'healthy';
} else {
    return 'degraded';
}
```

**✅ After**
```php
$checks = collect([$db, $cache, $queue, $redis]);
$state = $checks->ternaryMajority();

return $state->label(); // 'true' if majority healthy, 'false' if majority down, 'unknown' if tied
```

#### `whereTernaryTrue()` / `whereTernaryFalse()` / `whereTernaryUnknown()`
**❌ Before**
```php
$verified = $users->filter(fn($u) => $u->verified === true);
$unverified = $users->filter(fn($u) => $u->verified === false);
$pending = $users->filter(fn($u) => $u->verified === null);
```

**✅ After**
```php
$verified = $users->whereTernaryTrue('verified');
$unverified = $users->whereTernaryFalse('verified');
$pending = $users->whereTernaryUnknown('verified');
```

#### `ternaryWeighted()` - Weighted Voting
**❌ Before**
```php
// Complex manual weighting
$legal = $doc->legal_approved ? 5 : 0;
$finance = $doc->finance_approved ? 3 : 0;
$manager = $doc->manager_approved ? 2 : 0;
$total = $legal + $finance + $manager;

if ($total >= 7) {
    return 'approved';
}
```

**✅ After**
```php
$state = collect([
    $doc->legal_approved,
    $doc->finance_approved,
    $doc->manager_approved
])->ternaryWeighted([5, 3, 2]);

return $state->isTrue(); // true if weighted consensus is positive
```

#### `partitionTernary()` - Three-Way Split
**❌ Before**
```php
$true = [];
$false = [];
$unknown = [];

foreach ($users as $user) {
    if ($user->verified === true) {
        $true[] = $user;
    } elseif ($user->verified === false) {
        $false[] = $user;
    } else {
        $unknown[] = $user;
    }
}
```

**✅ After**
```php
[$verified, $unverified, $pending] = $users->partitionTernary('verified');
```

---

### 3. 🗄️ Eloquent Scopes (8 methods)

#### `whereTernaryTrue()` / `whereTernaryFalse()` / `whereTernaryUnknown()`
**❌ Before**
```php
// Fragile SQL - breaks if column format changes
$users = User::where('verified', true)
    ->orWhere('verified', 1)
    ->orWhere('verified', 'true')
    ->get();
```

**✅ After**
```php
$users = User::whereTernaryTrue('verified')->get();
// Handles: true, 1, '1', 'true', 'yes', 'on' automatically
```

#### `whereAllTernaryTrue()` / `whereAnyTernaryTrue()`
**❌ Before**
```php
// Nested where clauses
$users = User::where(function($q) {
    $q->where('verified', true)
      ->where('active', true)
      ->where('consented', true);
})->get();
```

**✅ After**
```php
$users = User::whereAllTernaryTrue(['verified', 'active', 'consented'])->get();
```

#### `orderByTernary()` - Smart Sorting
**❌ Before**
```php
// Complex CASE statements
$users = User::orderByRaw("
    CASE 
        WHEN verified = 1 THEN 3
        WHEN verified IS NULL THEN 2
        ELSE 1
    END DESC
")->get();
```

**✅ After**
```php
$users = User::orderByTernary('verified', 'desc')->get();
// TRUE first, UNKNOWN second, FALSE last
```

---

### 4. 🌐 Request Macros (5 methods)

#### `ternary()` - Input Normalization
**❌ Before**
```php
$consent = $request->input('consent');
$state = null;

if ($consent === 'true' || $consent === '1' || $consent === 1 || $consent === true) {
    $state = TernaryState::TRUE;
} elseif ($consent === 'false' || $consent === '0' || $consent === 0 || $consent === false) {
    $state = TernaryState::FALSE;
} else {
    $state = TernaryState::UNKNOWN;
}
```

**✅ After**
```php
$state = $request->ternary('consent');
```

#### `ternaryGate()` - Multi-Field Validation
**❌ Before**
```php
$verified = $request->input('verified');
$active = $request->input('active');
$consented = $request->input('consented');

if ($verified && $active && $consented) {
    // proceed
}
```

**✅ After**
```php
$state = $request->ternaryGate(['verified', 'active', 'consented'], 'and');

if ($state->isTrue()) {
    // proceed with confidence
}
```

---

### 5. 🎨 Blade Directives (10+)

#### `@ternaryTrue` / `@ternaryFalse` / `@ternaryUnknown`
**❌ Before**
```blade
@if($user->verified === true)
    <span class="badge badge-success">Verified</span>
@elseif($user->verified === false)
    <span class="badge badge-danger">Not Verified</span>
@else
    <span class="badge badge-warning">Pending</span>
@endif
```

**✅ After**
```blade
@ternaryTrue($user->verified)
    <span class="badge badge-success">Verified</span>
@endternaryTrue

@ternaryFalse($user->verified)
    <span class="badge badge-danger">Not Verified</span>
@endternaryFalse

@ternaryUnknown($user->verified)
    <span class="badge badge-warning">Pending</span>
@endternaryUnknown
```

#### `@ternaryMatch` - Template Pattern Matching
**❌ Before**
```blade
@php
$status = ternary($order->approved);
@endphp

@if($status->isTrue())
    <div class="alert alert-success">Order Approved</div>
@elseif($status->isFalse())
    <div class="alert alert-danger">Order Rejected</div>
@else
    <div class="alert alert-warning">Order Pending</div>
@endif
```

**✅ After**
```blade
@ternaryMatch($order->approved, [
    'true' => '<div class="alert alert-success">Order Approved</div>',
    'false' => '<div class="alert alert-danger">Order Rejected</div>',
    'unknown' => '<div class="alert alert-warning">Order Pending</div>'
])
```

#### `@allTrue` / `@anyTrue` - Multi-Check Gates
**❌ Before**
```blade
@if($user->verified && $user->active && !$user->blocked)
    <button class="btn-primary">Proceed</button>
@endif
```

**✅ After**
```blade
@allTrue([$user->verified, $user->active, !$user->blocked])
    <button class="btn-primary">Proceed</button>
@endallTrue
```

---

### 6. 🛡️ Middleware

#### `TernaryGateMiddleware`
**❌ Before**
```php
// In middleware
public function handle($request, Closure $next)
{
    $user = $request->user();
    
    if (!$user->verified || !$user->active || $user->blocked) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    return $next($request);
}

// In routes
Route::middleware([CustomVerificationMiddleware::class])->group(function() {
    // routes
});
```

**✅ After**
```php
// In routes - no custom middleware needed!
Route::middleware('ternary.gate:verified,active,!blocked,and,true')
    ->group(function() {
        // routes - automatically validated
    });
```

---

### 7. ✅ Validation Rules

#### Basic Rules
**❌ Before**
```php
$request->validate([
    'consent' => 'required|boolean',
    'terms' => 'required|accepted',
]);

// Problem: doesn't handle "unknown" gracefully
```

**✅ After**
```php
$request->validate([
    'consent' => 'required|ternary|ternary_true',
    'terms' => 'ternary_not_false', // Allows TRUE or UNKNOWN
]);
```

#### Advanced: Multi-Field Gates
**❌ Before**
```php
$request->validate([
    'email_verified' => 'required|boolean',
    'phone_verified' => 'required|boolean',
]);

// Custom logic needed to check if at least one is true
$emailVerified = $request->input('email_verified');
$phoneVerified = $request->input('phone_verified');

if (!$emailVerified && !$phoneVerified) {
    throw ValidationException::withMessages([
        'verification' => 'At least one verification method required'
    ]);
}
```

**✅ After**
```php
$request->validate([
    'verification' => [
        'ternary_gate:email_verified,phone_verified,or,true'
    ]
]);
```

---

### 8. 🧮 Advanced Features

#### Decision Engine with Blueprints
**❌ Before**
```php
// Complex approval logic scattered across codebase
$legalApproved = $doc->legal_status === 'approved';
$financeApproved = $doc->finance_status === 'approved';
$managerApproved = $doc->manager_status === 'approved';

if ($legalApproved && $financeApproved) {
    if ($managerApproved || $doc->priority === 'high') {
        $doc->publish();
    }
}
```

**✅ After**
```php
use VinkiusLabs\Trilean\Decision\TernaryDecisionEngine;

$engine = app(TernaryDecisionEngine::class);

$report = $engine->evaluate([
    'name' => 'document_approval',
    'inputs' => [
        'legal' => $doc->legal_status === 'approved',
        'finance' => $doc->finance_status === 'approved',
        'manager' => $doc->manager_status === 'approved',
        'high_priority' => $doc->priority === 'high',
    ],
    'gates' => [
        'core_approved' => [
            'operator' => 'and',
            'operands' => ['legal', 'finance'],
        ],
        'can_publish' => [
            'operator' => 'or',
            'operands' => ['core_approved', 'manager', 'high_priority'],
        ],
    ],
    'output' => 'can_publish'
]);

if ($report->result()->isTrue()) {
    $doc->publish();
}

// Bonus: Full audit trail
Log::info('Decision trace', $report->toArray());
```

#### Balanced Ternary Arithmetic
**❌ Before**
```php
// Standard binary arithmetic with explicit null handling
$scores = [1, -1, 0, null, 1];
$total = 0;

foreach ($scores as $score) {
    if ($score !== null) {
        $total += $score;
    }
}
```

**✅ After**
```php
use VinkiusLabs\Trilean\Support\TernaryArithmetic;

$arithmetic = new TernaryArithmetic();
$total = $arithmetic->sum([
    TernaryState::TRUE,   // +1
    TernaryState::FALSE,  // -1
    TernaryState::UNKNOWN, // 0
    TernaryState::TRUE,   // +1
]);

// Result: +1 (balanced ternary sum)
```

---

## 🎯 Real-World Use Cases

### 1. Feature Flags with Gradual Rollout
**Scenario**: Roll out a new payment gateway to 10% of users, with explicit handling for uncertain states.

**❌ Before**
```php
class PaymentController
{
    public function process(Request $request)
    {
        $newGatewayEnabled = Feature::get('new_payment_gateway');
        
        // What if the feature flag service is down? null breaks everything
        if ($newGatewayEnabled === true && rand(1, 100) <= 10) {
            return $this->processNewGateway($request);
        }
        
        return $this->processLegacyGateway($request);
    }
}
```

**✅ After**
```php
class PaymentController
{
    public function process(Request $request)
    {
        $rolloutState = ternary(Feature::get('new_payment_gateway'));
        
        return maybe($rolloutState,
            ifTrue: fn() => rand(1, 100) <= 10 
                ? $this->processNewGateway($request)
                : $this->processLegacyGateway($request),
            ifFalse: fn() => $this->processLegacyGateway($request),
            ifUnknown: fn() => $this->processLegacyGateway($request)->with('notice', 'Using fallback payment system')
        );
    }
}
```

### 2. Multi-Department Approval Workflow
**Scenario**: Document needs approval from legal (required), finance (required), and manager (optional).

**❌ Before**
```php
class DocumentApprovalService
{
    public function canPublish(Document $doc): bool
    {
        if ($doc->legal_approved !== true) {
            return false;
        }
        
        if ($doc->finance_approved !== true) {
            return false;
        }
        
        // Manager approval is optional, but null is treated as false
        // This is a bug waiting to happen!
        return true;
    }
    
    public function getStatus(Document $doc): string
    {
        if (!$doc->legal_approved) {
            return 'Waiting for legal';
        }
        if (!$doc->finance_approved) {
            return 'Waiting for finance';
        }
        return 'Approved';
    }
}
```

**✅ After**
```php
use VinkiusLabs\Trilean\Decision\TernaryDecisionEngine;

class DocumentApprovalService
{
    public function __construct(
        private TernaryDecisionEngine $engine
    ) {}
    
    public function canPublish(Document $doc): bool
    {
        $report = $this->engine->evaluate([
            'name' => 'document_approval',
            'inputs' => [
                'legal' => $doc->legal_approved,
                'finance' => $doc->finance_approved,
                'manager' => $doc->manager_approved,
            ],
            'gates' => [
                'required_approvals' => [
                    'operator' => 'and',
                    'operands' => ['legal', 'finance'],
                    'description' => 'Legal and finance must both approve',
                ],
                'final_decision' => [
                    'operator' => 'weighted',
                    'operands' => ['required_approvals', 'manager'],
                    'weights' => [10, 1], // Required approvals heavily weighted
                    'description' => 'Manager approval is a bonus',
                ],
            ],
            'output' => 'final_decision'
        ]);
        
        // Full decision audit trail available
        Log::info('Approval decision', $report->toArray());
        
        return $report->result()->isTrue();
    }
    
    public function getStatus(Document $doc): string
    {
        $legal = ternary($doc->legal_approved);
        $finance = ternary($doc->finance_approved);
        
        if ($legal->isFalse() || $finance->isFalse()) {
            return 'Rejected';
        }
        
        if ($legal->isUnknown()) {
            return 'Waiting for legal review';
        }
        
        if ($finance->isUnknown()) {
            return 'Waiting for finance review';
        }
        
        return 'Approved - ready to publish';
    }
}
```

### 3. System Health Monitoring
**Scenario**: Monitor multiple services and provide clear degradation signals.

**❌ Before**
```php
class HealthCheckController
{
    public function status()
    {
        $db = $this->checkDatabase();
        $cache = $this->checkCache();
        $queue = $this->checkQueue();
        $storage = $this->checkStorage();
        
        $healthy = 0;
        $total = 4;
        
        if ($db) $healthy++;
        if ($cache) $healthy++;
        if ($queue) $healthy++;
        if ($storage) $healthy++;
        
        if ($healthy === $total) {
            $status = 'healthy';
        } elseif ($healthy >= $total / 2) {
            $status = 'degraded';
        } else {
            $status = 'critical';
        }
        
        return response()->json([
            'status' => $status,
            'services' => [
                'database' => $db ? 'up' : 'down',
                'cache' => $cache ? 'up' : 'down',
                'queue' => $queue ? 'up' : 'down',
                'storage' => $storage ? 'up' : 'down',
            ]
        ]);
    }
}
```

**✅ After**
```php
class HealthCheckController
{
    public function status()
    {
        $vector = ternary_vector([
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ]);
        
        $score = $vector->score(); // +4 to -4 range
        $majority = $vector->majority();
        [$healthy, $unhealthy, $unknown] = $vector->partition();
        
        return response()->json([
            'status' => $majority->label(),
            'score' => $score,
            'health_percentage' => ($score + 4) / 8 * 100,
            'services' => [
                'healthy' => $healthy->keys()->all(),
                'unhealthy' => $unhealthy->keys()->all(),
                'unknown' => $unknown->keys()->all(),
            ],
            'consensus' => $vector->consensus()->value,
        ]);
    }
}
```

### 4. User Permissions with Uncertainty
**Scenario**: Check user permissions where some may be pending review.

**❌ Before**
```php
class PermissionChecker
{
    public function canAccessResource(User $user, Resource $resource): bool
    {
        // If any permission is null, we don't know what to do
        $hasRole = $user->hasRole('editor');
        $hasDirectPermission = $resource->hasDirectPermission($user);
        $teamAccess = $user->team?->hasAccessTo($resource);
        
        return $hasRole || $hasDirectPermission || $teamAccess;
    }
}
```

**✅ After**
```php
class PermissionChecker
{
    public function canAccessResource(User $user, Resource $resource): array
    {
        $state = consensus(
            $user->hasRole('editor'),
            $resource->hasDirectPermission($user),
            $user->team?->hasAccessTo($resource)
        );
        
        return ternary_match($state, [
            'true' => [
                'access' => 'granted',
                'reason' => 'User has sufficient permissions',
            ],
            'false' => [
                'access' => 'denied',
                'reason' => 'User lacks required permissions',
            ],
            'unknown' => [
                'access' => 'pending',
                'reason' => 'Some permissions are under review',
                'action' => 'Contact administrator',
            ],
        ]);
    }
}
```

---

## 🚀 Installation & Setup

```bash
composer require vinkius-labs/trilean
```

### Publish Configuration
```bash
php artisan vendor:publish --tag=trilean-config
```

### Optional: Publish Views
```bash
php artisan vendor:publish --tag=trilean-views
```

### Configure (optional)
```php
// config/trilean.php
return [
    'metrics' => [
        'enabled' => env('TRILEAN_METRICS', false),
        // ... metrics configuration
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

## 📖 Detailed Documentation

- **[Global Helpers](./en/global-helpers.md)** - All 10 helper functions with examples
- **[Collection Macros](./en/collection-macros.md)** - 12 Collection methods for ternary logic
- **[Eloquent Scopes](./en/eloquent-scopes.md)** - Database queries with ternary states
- **[Request Macros](./en/request-macros.md)** - HTTP request ternary handling
- **[Blade Directives](./en/blade-directives.md)** - Template directives for views
- **[Validation Rules](./en/validation-rules.md)** - Form validation with ternary logic
- **[Middleware](./en/middleware.md)** - Route protection with ternary gates
- **[Advanced Capabilities](./en/advanced-capabilities.md)** - Decision Engine, Arithmetic, Circuits
- **[Use Cases](./en/use-cases.md)** - Real-world implementation patterns

---

## 🎨 Future Ideas

- **Ternary Cache** - Cache layer that respects state changes
- **Real-Time Monitor** - Dashboard for ternary decision streams
- **Automatic Policies** - Generate Gate policies from ternary rules
- **Decision Replay** - Audit and replay decisions for debugging
- **Artisan Inspector** - CLI tool to inspect ternary states across your app

---

## 📄 License

MIT License - see [LICENSE](../LICENSE) file for details.

---

**Built with ❤️ by VinkiusLabs** | [GitHub](https://github.com/vinkius-labs/trilean) | [Issues](https://github.com/vinkius-labs/trilean/issues)
