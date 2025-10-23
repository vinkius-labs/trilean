<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class RequestMacrosTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_request_macros_convert_values(): void
    {
        $request = Request::create('/', 'POST', [
            'consent' => 'yes',
            'verified' => 'no',
            'pending' => null,
        ]);

        $this->assertTrue($request->ternary('consent')->isTrue());
        $this->assertTrue($request->hasTernaryTrue('consent'));
        $this->assertTrue($request->hasTernaryFalse('verified'));
        $this->assertTrue($request->hasTernaryUnknown('pending'));
    }

    public function test_request_ternary_gate_and_expression(): void
    {
        $request = Request::create('/', 'POST', [
            'consent' => true,
            'active' => false,
            'blocked' => false,
        ]);

        $gate = $request->ternaryGate(['consent', 'active'], 'or');
        $this->assertInstanceOf(TernaryState::class, $gate);
        $this->assertTrue($gate->isTrue());

        $expression = $request->ternaryExpression('consent AND !blocked');
        $this->assertTrue($expression->isTrue());
    }
}
