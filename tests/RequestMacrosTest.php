<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class RequestMacrosTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_is_true_macro_works()
    {
        $request = Request::create('/', 'POST', ['verified' => 'yes']);
        $this->assertTrue($request->isTrue('verified'));

        $request = Request::create('/', 'POST', ['verified' => 'no']);
        $this->assertFalse($request->isTrue('verified'));
    }

    public function test_is_false_macro_works()
    {
        $request = Request::create('/', 'POST', ['blocked' => 'no']);
        $this->assertTrue($request->isFalse('blocked'));

        $request = Request::create('/', 'POST', ['blocked' => 'yes']);
        $this->assertFalse($request->isFalse('blocked'));
    }

    public function test_is_unknown_macro_works()
    {
        $request = Request::create('/', 'POST', ['consent' => null]);
        $this->assertTrue($request->isUnknown('consent'));

        $request = Request::create('/', 'POST', ['consent' => 'yes']);
        $this->assertFalse($request->isUnknown('consent'));
    }

    public function test_pick_macro_works()
    {
        $request = Request::create('/', 'POST', ['status' => true]);
        $result = $request->pick('status', 'Active', 'Inactive');
        $this->assertEquals('Active', $result);

        $request = Request::create('/', 'POST', ['status' => false]);
        $result = $request->pick('status', 'Active', 'Inactive');
        $this->assertEquals('Inactive', $result);
    }

    public function test_all_true_macro_works()
    {
        $request = Request::create('/', 'POST', [
            'verified' => 'yes',
            'consent' => true,
            'active' => 1
        ]);
        $this->assertTrue($request->allTrue(['verified', 'consent', 'active']));

        $request = Request::create('/', 'POST', [
            'verified' => 'yes',
            'consent' => false,
            'active' => 1
        ]);
        $this->assertFalse($request->allTrue(['verified', 'consent', 'active']));
    }

    public function test_any_true_macro_works()
    {
        $request = Request::create('/', 'POST', [
            'method1' => false,
            'method2' => true,
            'method3' => false
        ]);
        $this->assertTrue($request->anyTrue(['method1', 'method2', 'method3']));

        $request = Request::create('/', 'POST', [
            'method1' => false,
            'method2' => false,
            'method3' => false
        ]);
        $this->assertFalse($request->anyTrue(['method1', 'method2', 'method3']));
    }

    public function test_vote_macro_works()
    {
        $request = Request::create('/', 'POST', [
            'check1' => true,
            'check2' => true,
            'check3' => false
        ]);
        $result = $request->vote(['check1', 'check2', 'check3']);
        $this->assertEquals('true', $result);
    }

    public function test_require_true_macro_throws_when_not_true()
    {
        $request = Request::create('/', 'POST', ['verified' => false]);

        $this->expectException(\InvalidArgumentException::class);
        $request->requireTrue('verified');
    }

    public function test_require_not_false_macro_throws_when_false()
    {
        $request = Request::create('/', 'POST', ['blocked' => 'no']);

        $this->expectException(\InvalidArgumentException::class);
        $request->requireNotFalse('blocked');
    }
}
