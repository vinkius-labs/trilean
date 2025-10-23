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
        $compiled = Blade::compileString('@ternary(true) allowed @endternary');
        $this->assertStringContainsString('<?php if (ternary(true)->isTrue()): ?>', $compiled);
        $this->assertStringContainsString('<?php endif; ?>', $compiled);

        $unknownCompiled = Blade::compileString('@ternaryUnknown(null) pending @endternaryUnknown');
        $this->assertStringContainsString('<?php if (ternary(null)->isUnknown()): ?>', $unknownCompiled);
        $this->assertStringContainsString('<?php endif; ?>', $unknownCompiled);
    }

    public function test_maybe_and_badge_directives(): void
    {
        $maybe = Blade::render('@maybe(true, "Y", "N", "?")');
        $this->assertSame('Y', trim($maybe));

        $badge = Blade::render('@ternaryBadge("false")');
        $this->assertStringContainsString('inline-flex items-center', $badge);
    }

    public function test_ternary_match_assigns_value(): void
    {
        $output = Blade::render("@ternaryMatch('true', ['true' => 'Aprovado'])");

        $this->assertSame('Aprovado', trim($output));
    }
}
