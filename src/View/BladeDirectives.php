<?php

namespace VinkiusLabs\Trilean\View;

use Illuminate\Support\Facades\Blade;
use VinkiusLabs\Trilean\Enums\TernaryState;

class BladeDirectives
{
    public static function register(): void
    {
        /**
         * @ternary directive - conditional based on ternary state.
         * 
         * @ternary($user->verified)
         *     <div>User is verified</div>
         * @elseternary
         *     <div>User is not verified or unknown</div>
         * @endternary
         */
        Blade::if('ternary', function ($value) {
            return ternary($value)->isTrue();
        });

        /**
         * @ternaryTrue - check if value is TRUE.
         * 
         * @ternaryTrue($user->active)
         *     <span class="badge-success">Active</span>
         * @endternaryTrue
         */
        Blade::if('ternaryTrue', function ($value) {
            return ternary($value)->isTrue();
        });

        /**
         * @ternaryFalse - check if value is FALSE.
         */
        Blade::if('ternaryFalse', function ($value) {
            return ternary($value)->isFalse();
        });

        /**
         * @ternaryUnknown - check if value is UNKNOWN.
         * 
         * @ternaryUnknown($user->consent)
         *     <div class="alert alert-warning">Consent pending</div>
         * @endternaryUnknown
         */
        Blade::if('ternaryUnknown', function ($value) {
            return ternary($value)->isUnknown();
        });

        /**
         * @maybe directive - three-way conditional rendering.
         * 
         * Usage in blade (custom directive):
         * {{ maybe($condition, 'Yes', 'No', 'Maybe') }}
         */
        Blade::directive('maybe', function ($expression) {
            return "<?php echo maybe({$expression}); ?>";
        });

        /**
         * @ternaryMatch - pattern matching in blade.
         * 
         * @ternaryMatch($user->status)
         *     @case('true')
         *         <span class="text-success">Approved</span>
         *     @case('false')
         *         <span class="text-danger">Rejected</span>
         *     @case('unknown')
         *         <span class="text-warning">Pending</span>
         * @endternaryMatch
         */
        Blade::directive('ternaryMatch', function ($expression) {
            return "<?php \$__ternaryMatchValue = ternary({$expression})->value; ?>";
        });

        Blade::directive('case', function ($expression) {
            return "<?php if (\$__ternaryMatchValue === {$expression}): ?>";
        });

        Blade::directive('endternaryMatch', function () {
            return "<?php endif; unset(\$__ternaryMatchValue); ?>";
        });

        /**
         * @ternaryBadge - render a styled badge based on ternary state.
         * 
         * @ternaryBadge($user->verified)
         * Outputs: <span class="badge badge-success">True</span>
         */
        Blade::directive('ternaryBadge', function ($expression) {
            return "<?php echo view('trilean::components.badge', ['state' => {$expression}])->render(); ?>";
        });

        /**
         * @ternaryIcon - render an icon based on ternary state.
         * 
         * @ternaryIcon($permission, 'check', 'times', 'question')
         */
        Blade::directive('ternaryIcon', function ($expression) {
            return <<<'PHP'
<?php 
    $parts = array_map('trim', explode(',', $expression));
    $value = array_shift($parts);
    $state = ternary($value);
    $icons = config('trilean.ui.icons');

    if (count($parts) === 3) {
        $icons = [
            'true' => trim($parts[0], " '""),
            'false' => trim($parts[1], " '""),
            'unknown' => trim($parts[2], " '""),
        ];
    }

    $iconClass = $icons[$state->value] ?? 'icon-question-mark-circle';
    echo "<i class='{$iconClass}'></i>";
?>
PHP;
        });

        /**
         * @allTrue - check if all values are TRUE.
         * 
         * @allTrue([$user->verified, $user->active, $user->consented])
         *     <button>Proceed</button>
         * @endallTrue
         */
        Blade::if('allTrue', function ($values) {
            return all_true(...(is_array($values) ? $values : [$values]));
        });

        /**
         * @anyTrue - check if any value is TRUE.
         */
        Blade::if('anyTrue', function ($values) {
            return any_true(...(is_array($values) ? $values : [$values]));
        });
    }
}
