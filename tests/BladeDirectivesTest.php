<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class BladeDirectivesTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_boolean_conditionals_render_expected_sections(): void
    {
        $output = trim(Blade::render('@ternary(true)allowed@endternary'));
        $this->assertSame('allowed', $output);

        $unknown = trim(Blade::render('@ternaryUnknown(null)pending@endternaryUnknown'));
        $this->assertSame('pending', $unknown);
    }

    public function test_maybe_and_badge_directives(): void
    {
        $maybe = Blade::render('@maybe(true, "Y", "N", "?")');
        $this->assertSame('Y', trim($maybe));

        $badge = Blade::render('@ternaryBadge("false")');
        $this->assertStringContainsString('badge', $badge);
    }

    public function test_ternary_match_assigns_value(): void
    {
        $output = Blade::render(<<<'BLADE'
@ternaryMatch("true")
    @case('true')Aprovado
@endternaryMatch
BLADE);

        $this->assertStringContainsString('Aprovado', $output);
    }
}
