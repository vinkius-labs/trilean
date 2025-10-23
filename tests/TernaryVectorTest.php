<?php

namespace VinkiusLabs\Trilean\Tests;

use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Collections\TernaryVector;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class TernaryVectorTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_vector_combinators(): void
    {
        $vector = TernaryVector::make([true, false, true]);

        $this->assertSame(TernaryState::TRUE, $vector->or());
        $this->assertSame(TernaryState::FALSE, $vector->and());
        $this->assertSame(TernaryState::TRUE, $vector->consensus());
        $this->assertSame(TernaryState::TRUE, $vector->majority());
    }

    public function test_balanced_string_and_score(): void
    {
        $vector = TernaryVector::make([TernaryState::TRUE, TernaryState::UNKNOWN, TernaryState::FALSE]);

        $this->assertSame('+0-', $vector->toBalancedString());
        $this->assertSame(0, $vector->score());
    }
}
