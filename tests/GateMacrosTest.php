<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Support\Facades\Gate;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class GateMacrosTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_define_ternary_allows_truthy_results(): void
    {
        Gate::defineTernary('feature-access', fn() => TernaryState::TRUE);

        $this->assertTrue(Gate::allows('feature-access'));
    }

    public function test_unknown_state_uses_fallback_and_inspection(): void
    {
        config()->set('trilean.policies.unknown_resolves_to', true);

        Gate::defineTernary('soft-feature', fn() => TernaryState::UNKNOWN);

        $this->assertTrue(Gate::allows('soft-feature'));

        $inspection = Gate::inspectTernary('soft-feature');
        $this->assertArrayHasKey('allowed', $inspection);
        $this->assertArrayHasKey('metadata', $inspection);
    }
}
