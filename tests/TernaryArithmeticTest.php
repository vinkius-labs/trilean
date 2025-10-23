<?php

namespace VinkiusLabs\Trilean\Tests;

use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Support\TernaryArithmetic;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class TernaryArithmeticTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_service_provider_binds_arithmetic(): void
    {
        $arithmetic = $this->app->make(TernaryArithmetic::class);

        $this->assertInstanceOf(TernaryArithmetic::class, $arithmetic);
    }

    public function test_add_and_subtract_in_balanced_ternary(): void
    {
        $arithmetic = $this->app->make(TernaryArithmetic::class);

        $this->assertSame(2, $arithmetic->add(5, -3));
        $this->assertSame(8, $arithmetic->add(5, 3));
        $this->assertSame(2, $arithmetic->subtract(5, 3));
    }

    public function test_normalize_noise_handles_unknown_majority_and_empty_array(): void
    {
        $arithmetic = $this->app->make(TernaryArithmetic::class);

        $values = [
            TernaryState::UNKNOWN,
            TernaryState::TRUE,
            TernaryState::UNKNOWN,
        ];

        $normalised = $arithmetic->normalizeNoise($values, threshold: 0.5);

        $this->assertCount(3, $normalised);
        $this->assertTrue(TernaryState::fromMixed($normalised[0])->isTrue());
        $this->assertSame([], $arithmetic->normalizeNoise([]));
    }
}
