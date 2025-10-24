<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class BladeDirectivesTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_true_directive_compiles_correctly()
    {
        $compiled = Blade::compileString('@true($verified) Verified @endtrue');
        $this->assertStringContainsString('is_true', $compiled);
        $this->assertStringContainsString('endif', $compiled);
    }

    public function test_false_directive_compiles_correctly()
    {
        $compiled = Blade::compileString('@false($blocked) Not Blocked @endfalse');
        $this->assertStringContainsString('is_false', $compiled);
        $this->assertStringContainsString('endif', $compiled);
    }

    public function test_unknown_directive_compiles_correctly()
    {
        $compiled = Blade::compileString('@unknown($pending) Pending @endunknown');
        $this->assertStringContainsString('is_unknown', $compiled);
        $this->assertStringContainsString('endif', $compiled);
    }

    public function test_pick_directive_compiles_correctly()
    {
        $compiled = Blade::compileString('@pick($status, "Active", "Inactive")');
        $this->assertStringContainsString('pick', $compiled);
        $this->assertStringContainsString('echo', $compiled);
    }

    public function test_vote_directive_outputs_result()
    {
        $result = Blade::render('@vote(true, true, false)');
        $this->assertEquals('true', trim($result));
    }

    public function test_all_directive_compiles_correctly()
    {
        $compiled = Blade::compileString('@all($a, $b) All True @endall');
        $this->assertStringContainsString('and_all', $compiled);
    }

    public function test_any_directive_compiles_correctly()
    {
        $compiled = Blade::compileString('@any($a, $b) Any True @endany');
        $this->assertStringContainsString('or_any', $compiled);
    }

    public function test_safe_directive_outputs_boolean()
    {
        $compiled = Blade::compileString('@safe($value, false)');
        $this->assertStringContainsString('safe_bool', $compiled);
    }
}
