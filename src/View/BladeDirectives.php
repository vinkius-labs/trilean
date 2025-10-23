<?php

namespace VinkiusLabs\Trilean\View;

use Illuminate\Support\Facades\Blade;

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
        Blade::directive('ternary', fn($expression) => "<?php if (ternary({$expression})->isTrue()): ?>");
        Blade::directive('elseternary', fn() => '<?php else: ?>');
        Blade::directive('endternary', fn() => '<?php endif; ?>');

        /**
         * @ternaryTrue - check if value is TRUE.
         * 
         * @ternaryTrue($user->active)
         *     <span class="badge-success">Active</span>
         * @endternaryTrue
         */
        Blade::directive('ternaryTrue', fn($expression) => "<?php if (ternary({$expression})->isTrue()): ?>");
        Blade::directive('endternaryTrue', fn() => '<?php endif; ?>');

        /**
         * @ternaryFalse - check if value is FALSE.
         */
        Blade::directive('ternaryFalse', fn($expression) => "<?php if (ternary({$expression})->isFalse()): ?>");
        Blade::directive('endternaryFalse', fn() => '<?php endif; ?>');

        /**
         * @ternaryUnknown - check if value is UNKNOWN.
         * 
         * @ternaryUnknown($user->consent)
         *     <div class="alert alert-warning">Consent pending</div>
         * @endternaryUnknown
         */
        Blade::directive('ternaryUnknown', fn($expression) => "<?php if (ternary({$expression})->isUnknown()): ?>");
        Blade::directive('endternaryUnknown', fn() => '<?php endif; ?>');

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
         * @ternaryMatch - lightweight pattern matching helper.
         * 
         * @ternaryMatch($user->status, [
         *     'true' => '<span class="text-success">Approved</span>',
         *     'false' => '<span class="text-danger">Rejected</span>',
         *     'unknown' => '<span class="text-warning">Pending</span>',
         * ])
         */
        Blade::directive('ternaryMatch', function ($expression) {
            [$value, $cases] = array_pad(explode(',', $expression, 2), 2, '[]');

            $value = trim($value);
            $cases = trim($cases);

            return "<?php echo ternary_match({$value}, {$cases}); ?>";
        });

        Blade::directive('endternaryMatch', fn() => '');

        /**
         * @ternaryBadge - render a styled badge based on ternary state.
         * 
         * @ternaryBadge($user->verified)
         * Outputs: <span class="badge badge-success">True</span>
         */
        Blade::directive('ternaryBadge', function ($expression) {
            return "<?php echo ternary_badge({$expression}); ?>";
        });

        /**
         * @ternaryIcon - render an icon based on ternary state.
         * 
         * @ternaryIcon($permission, 'check', 'times', 'question')
         */
        Blade::directive('ternaryIcon', function ($expression) {
            return "<?php echo ternary_icon({$expression}); ?>";
        });

        /**
         * @allTrue - check if all values are TRUE.
         * 
         * @allTrue([$user->verified, $user->active, $user->consented])
         *     <button>Proceed</button>
         * @endallTrue
         */
        Blade::directive('allTrue', function ($expression) {
            $values = '$__ternaryAllTrueValues';

            return <<<PHP
<?php
    {$values} = {$expression};
    if (all_true(...(is_array({$values}) ? {$values} : [{$values}]))): ?>
PHP;
        });
        Blade::directive('endallTrue', fn() => '<?php endif; unset($__ternaryAllTrueValues); ?>');

        /**
         * @anyTrue - check if any value is TRUE.
         */
        Blade::directive('anyTrue', function ($expression) {
            $values = '$__ternaryAnyTrueValues';

            return <<<PHP
<?php
    {$values} = {$expression};
    if (any_true(...(is_array({$values}) ? {$values} : [{$values}]))): ?>
PHP;
        });
        Blade::directive('endanyTrue', fn() => '<?php endif; unset($__ternaryAnyTrueValues); ?>');
    }
}
