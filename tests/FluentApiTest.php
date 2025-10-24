<?php

namespace VinkiusLabs\Trilean\Tests;

use PHPUnit\Framework\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;

class FluentApiTest extends TestCase
{
    /** @test */
    public function it_can_use_fluent_if_methods()
    {
        $result = ternary(true)
            ->ifTrue('Premium')
            ->ifFalse('Free')
            ->ifUnknown('Trial')
            ->resolve();

        $this->assertEquals('Premium', $result);
    }

    /** @test */
    public function it_can_chain_when_callbacks()
    {
        $executed = [];

        ternary(true)
            ->whenTrue(function () use (&$executed) {
                $executed[] = 'true';
            })
            ->whenFalse(function () use (&$executed) {
                $executed[] = 'false';
            })
            ->whenUnknown(function () use (&$executed) {
                $executed[] = 'unknown';
            })
            ->execute();

        $this->assertEquals(['true'], $executed);
    }

    /** @test */
    public function it_can_pipe_transformations()
    {
        $result = ternary(true)
            ->pipe(fn($s) => $s->invert())
            ->toBool();

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_use_match_method()
    {
        $result = ternary('unknown')->match('Yes', 'No', 'Maybe');

        $this->assertEquals('Maybe', $result);
    }
}
