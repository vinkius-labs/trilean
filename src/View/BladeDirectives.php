<?php

namespace VinkiusLabs\Trilean\View;

use Illuminate\Support\Facades\Blade;

class BladeDirectives
{
    public static function register(): void
    {
        /**
         * Simple @true directive - most common case.
         * 
         * @true($user->verified)
         *     <div>User is verified</div>
         * @endtrue
         */
        Blade::directive('true', fn($expression) => "<?php if (is_true({$expression})): ?>");
        Blade::directive('endtrue', fn() => '<?php endif; ?>');

        /**
         * Simple @false directive.
         * 
         * @false($user->blocked)
         *     <div>User is not blocked</div>
         * @endfalse
         */
        Blade::directive('false', fn($expression) => "<?php if (is_false({$expression})): ?>");
        Blade::directive('endfalse', fn() => '<?php endif; ?>');

        /**
         * Simple @unknown directive.
         * 
         * @unknown($user->consent)
         *     <div>Consent pending</div>
         * @endunknown
         */
        Blade::directive('unknown', fn($expression) => "<?php if (is_unknown({$expression})): ?>");
        Blade::directive('endunknown', fn() => '<?php endif; ?>');

        /**
         * @pick directive - inline conditional.
         * 
         * @pick($user->active, 'Active', 'Inactive')
         * @pick($status, 'Yes', 'No', 'Maybe')
         */
        Blade::directive('pick', function ($expression) {
            return "<?php echo pick({$expression}); ?>";
        });

        /**
         * @vote directive - simple majority.
         * 
         * @vote($check1, $check2, $check3)
         */
        Blade::directive('vote', function ($expression) {
            return "<?php echo vote({$expression}); ?>";
        });

        /**
         * @all directive - check if all values are true.
         * 
         * @all($verified, $consented, $active)
         *     <button>Proceed</button>
         * @endall
         */
        Blade::directive('all', function ($expression) {
            return "<?php if (and_all({$expression})): ?>";
        });
        Blade::directive('endall', fn() => '<?php endif; ?>');

        /**
         * @any directive - check if any value is true.
         * 
         * @any($method1, $method2, $method3)
         *     <div>At least one method is available</div>
         * @endany
         */
        Blade::directive('any', function ($expression) {
            return "<?php if (or_any({$expression})): ?>";
        });
        Blade::directive('endany', fn() => '<?php endif; ?>');

        /**
         * @safe directive - convert to boolean with default.
         * 
         * {{ @safe($user->active, false) ? 'Active' : 'Inactive' }}
         */
        Blade::directive('safe', function ($expression) {
            return "<?php echo safe_bool({$expression}); ?>";
        });
    }
}
