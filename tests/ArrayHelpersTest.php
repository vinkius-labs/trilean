<?php

namespace VinkiusLabs\Trilean\Tests;

use PHPUnit\Framework\TestCase;

class ArrayHelpersTest extends TestCase
{
    /** @test */
    public function it_checks_all_true_in_array()
    {
        $this->assertTrue(array_all_true([true, 1, 'yes']));
        $this->assertFalse(array_all_true([true, false, 'yes']));
    }

    /** @test */
    public function it_checks_any_true_in_array()
    {
        $this->assertTrue(array_any_true([false, true, null]));
        $this->assertFalse(array_any_true([false, null, 0]));
    }

    /** @test */
    public function it_filters_true_values()
    {
        $values = [true, false, 'yes', null, 1, 0];
        $filtered = array_filter_true($values);

        $this->assertCount(3, $filtered); // true, 'yes', 1
    }

    /** @test */
    public function it_counts_ternary_states()
    {
        $values = [true, true, false, null, 'yes'];
        $counts = array_count_ternary($values);

        $this->assertEquals(3, $counts['true']);  // true, true, 'yes'
        $this->assertEquals(1, $counts['false']); // false
        $this->assertEquals(1, $counts['unknown']); // null
    }
}
