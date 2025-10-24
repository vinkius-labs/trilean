<?php

namespace VinkiusLabs\Trilean\Tests;

use PHPUnit\Framework\TestCase;

class PatternMatchingTest extends TestCase
{
    /** @test */
    public function it_can_match_with_wildcards()
    {
        $result = match_ternary('premium', [
            'premium|enterprise' => 'Advanced',
            'free|trial' => 'Basic',
            '*' => 'Unknown'
        ]);

        $this->assertEquals('Advanced', $result);
    }

    /** @test */
    public function it_can_match_pipe_separated_patterns()
    {
        $result = match_ternary('trial', [
            'premium|enterprise' => 'Advanced',
            'free|trial' => 'Basic',
            '*' => 'Unknown'
        ]);

        $this->assertEquals('Basic', $result);
    }

    /** @test */
    public function it_uses_default_wildcard()
    {
        $result = match_ternary('unknown_value', [
            'premium' => 'Advanced',
            '*' => 'Default'
        ]);

        $this->assertEquals('Default', $result);
    }
}
