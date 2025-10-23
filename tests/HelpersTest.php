<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class HelpersTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_ternary_helper_converts_values()
    {
        $this->assertTrue(ternary(true)->isTrue());
        $this->assertTrue(ternary(false)->isFalse());
        $this->assertTrue(ternary(null)->isUnknown());
        $this->assertSame('True', ternary(1)->label());
    }

    public function test_maybe_helper_returns_correct_branch()
    {
        $this->assertSame('yes', maybe(true, 'yes', 'no', 'maybe'));
        $this->assertSame('no', maybe(false, 'yes', 'no', 'maybe'));
        $this->assertSame('maybe', maybe(null, 'yes', 'no', 'maybe'));
    }

    public function test_all_true_helper()
    {
        $this->assertTrue(all_true(true, 1, 'yes', 'true'));
        $this->assertFalse(all_true(true, false, true));
        $this->assertFalse(all_true(true, null, true));
    }

    public function test_any_true_helper()
    {
        $this->assertTrue(any_true(false, null, true));
        $this->assertTrue(any_true(true, false, false));
        $this->assertFalse(any_true(false, false, false));
    }

    public function test_consensus_helper()
    {
        $this->assertSame(TernaryState::TRUE, consensus(true, true, true));
        $this->assertSame(TernaryState::TRUE, consensus(true, false, true));
    }

    public function test_when_ternary_executes_correct_callback()
    {
        $result = when_ternary(
            true,
            fn() => 'executed_true',
            fn() => 'executed_false',
            fn() => 'executed_unknown'
        );

        $this->assertSame('executed_true', $result);
    }

    public function test_ternary_match_helper()
    {
        $result = ternary_match(true, [
            'true' => 'Approved',
            'false' => 'Rejected',
            'unknown' => 'Pending',
        ]);

        $this->assertSame('Approved', $result);
    }
}
