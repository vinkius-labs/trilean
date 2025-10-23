<?php

namespace VinkiusLabs\Trilean\Tests;

use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Support\CircuitBuilder;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class CircuitBuilderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_it_evaluates_simple_circuit(): void
    {
        $builder = $this->app->make(CircuitBuilder::class);

        $result = $builder
            ->input('a', true)
            ->input('b', false)
            ->and('gate_and', ['a', 'b'])
            ->or('result', ['gate_and', 'a'])
            ->evaluate('result');

        $this->assertInstanceOf(TernaryState::class, $result);
        $this->assertTrue($result->isTrue());
    }

    public function test_it_exports_blueprint(): void
    {
        $builder = $this->app->make(CircuitBuilder::class);

        $builder
            ->input('signal', TernaryState::TRUE)
            ->maj('majority', ['signal', 'signal', true]);

        $blueprint = $builder->toBlueprint();

        $this->assertArrayHasKey('inputs', $blueprint);
        $this->assertArrayHasKey('gates', $blueprint);
        $this->assertArrayHasKey('output', $blueprint);
        $this->assertSame('majority', $blueprint['output']);
        $this->assertSame(['signal'], array_keys($blueprint['inputs']));
    }
}
