<?php

namespace VinkiusLabs\Trilean\Tests;

use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class HelpersTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_is_true_identifies_truthy_values()
    {
        $this->assertTrue(is_true(true));
        $this->assertTrue(is_true(1));
        $this->assertTrue(is_true('yes'));
        $this->assertFalse(is_true(false));
        $this->assertFalse(is_true(null));
    }

    public function test_is_false_identifies_falsy_values()
    {
        $this->assertTrue(is_false(false));
        $this->assertTrue(is_false(0));
        $this->assertTrue(is_false('no'));
        $this->assertFalse(is_false(true));
        $this->assertFalse(is_false(null));
    }

    public function test_is_unknown_identifies_unknown_values()
    {
        $this->assertTrue(is_unknown(null));
        $this->assertTrue(is_unknown('unknown'));
        $this->assertTrue(is_unknown('pending'));
        $this->assertTrue(is_unknown('maybe'));
        $this->assertFalse(is_unknown(true));
        $this->assertFalse(is_unknown(false));
        $this->assertFalse(is_unknown('yes'));
        $this->assertFalse(is_unknown('no'));
    }

    public function test_and_all_logic()
    {
        $this->assertTrue(and_all(true, 'yes', 1));
        $this->assertFalse(and_all(true, false));
        $this->assertFalse(and_all(true, null));
    }

    public function test_or_any_logic()
    {
        $this->assertTrue(or_any(false, true));
        $this->assertTrue(or_any(null, 'yes'));
        $this->assertFalse(or_any(false, false));
    }

    public function test_pick_function()
    {
        $this->assertEquals('Active', pick(true, 'Active', 'Inactive'));
        $this->assertEquals('Inactive', pick(false, 'Active', 'Inactive'));
        $this->assertEquals('Pending', pick(null, 'Active', 'Inactive', 'Pending'));
    }

    public function test_vote_function()
    {
        $this->assertEquals('true', vote(true, true, false));
        $this->assertEquals('false', vote(false, false, true));
        $this->assertEquals('tie', vote(true, false));
    }

    public function test_safe_bool_conversion()
    {
        $this->assertTrue(safe_bool(true));
        $this->assertFalse(safe_bool(false));
        $this->assertFalse(safe_bool(null, false));
        $this->assertTrue(safe_bool(null, true));
    }

    public function test_require_true_throws_when_not_true()
    {
        $this->expectException(\InvalidArgumentException::class);
        require_true(false);
    }

    public function test_require_not_false_throws_when_false()
    {
        $this->expectException(\InvalidArgumentException::class);
        require_not_false(false);
    }
}
