<?php

namespace VinkiusLabs\Trilean\Tests;

use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Collections\TernaryVector;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Support\BalancedTernaryConverter;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class BalancedTernaryConverterTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_it_encodes_states(): void
    {
        $converter = $this->app->make(BalancedTernaryConverter::class);

        $encoded = $converter->encodeStates([
            TernaryState::TRUE,
            TernaryState::UNKNOWN,
            TernaryState::FALSE,
        ]);

        $this->assertSame('+0-', $encoded);
    }

    public function test_it_decodes_state_vectors(): void
    {
        $converter = $this->app->make(BalancedTernaryConverter::class);

        $vector = $converter->decodeStates('+0-');

        $this->assertInstanceOf(TernaryVector::class, $vector);
        $this->assertEquals([
            TernaryState::TRUE,
            TernaryState::UNKNOWN,
            TernaryState::FALSE,
        ], $vector->all());
    }
}
