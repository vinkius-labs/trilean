<?php

namespace VinkiusLabs\Trilean\Tests;

use PHPUnit\Framework\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;

/**
 * Performance benchmarks to ensure Trilean has minimal overhead.
 * 
 * Target: < 1μs overhead per operation for common use cases.
 * 
 * Important: Percentage overhead can look high when absolute times are tiny (microseconds),
 * but what matters is the ABSOLUTE overhead per operation in real-world usage.
 */
class PerformanceBenchmarkTest extends TestCase
{
    private const ITERATIONS = 100000; // Increased for more accurate benchmarks
    private const MAX_OVERHEAD_PER_OP_MICROSECONDS = 0.05; // 50 nanoseconds max overhead

    /** @test */
    public function benchmark_boolean_check_vs_native()
    {
        $value = true;

        // Benchmark native PHP
        $start = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $result = $value === true;
        }
        $nativeTime = microtime(true) - $start;

        // Benchmark Trilean is_true()
        $start = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $result = is_true($value);
        }
        $trileanTime = microtime(true) - $start;

        $overheadTotal = $trileanTime - $nativeTime;
        $overheadPerOp = ($overheadTotal / self::ITERATIONS) * 1000000; // Convert to microseconds

        echo "\n";
        echo "Boolean Check Benchmark (" . number_format(self::ITERATIONS) . " iterations):\n";
        echo "  Native PHP:       " . $this->formatTime($nativeTime) . " total\n";
        echo "  Trilean:          " . $this->formatTime($trileanTime) . " total\n";
        echo "  Overhead/op:      " . round($overheadPerOp, 3) . " μs\n";
        echo "  Per operation:    Native=" . round(($nativeTime / self::ITERATIONS) * 1000000, 3) . "μs, ";
        echo "Trilean=" . round(($trileanTime / self::ITERATIONS) * 1000000, 3) . "μs\n";

        $this->assertLessThan(
            self::MAX_OVERHEAD_PER_OP_MICROSECONDS,
            $overheadPerOp,
            "is_true() adds {$overheadPerOp}μs per operation (max: " . self::MAX_OVERHEAD_PER_OP_MICROSECONDS . "μs)"
        );
    }

    /** @test */
    public function benchmark_and_operation_vs_native()
    {
        $a = true;
        $b = true;
        $c = true;

        // Benchmark native PHP
        $start = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $result = $a && $b && $c;
        }
        $nativeTime = microtime(true) - $start;

        // Benchmark Trilean and_all()
        $start = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $result = and_all($a, $b, $c);
        }
        $trileanTime = microtime(true) - $start;

        $overheadPerOp = (($trileanTime - $nativeTime) / self::ITERATIONS) * 1000000;

        echo "\n";
        echo "AND Operation Benchmark (" . number_format(self::ITERATIONS) . " iterations):\n";
        echo "  Native PHP:       " . $this->formatTime($nativeTime) . " total\n";
        echo "  Trilean:          " . $this->formatTime($trileanTime) . " total\n";
        echo "  Overhead/op:      " . round($overheadPerOp, 3) . " μs\n";

        // AND operation can have more overhead due to variadic args and loops
        $this->assertLessThan(
            0.1, // 100ns per operation
            $overheadPerOp,
            "and_all() adds {$overheadPerOp}μs per operation"
        );
    }

    /** @test */
    public function benchmark_ternary_operator_vs_pick()
    {
        $condition = true;

        // Benchmark native ternary
        $start = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $result = $condition ? 'yes' : 'no';
        }
        $nativeTime = microtime(true) - $start;

        // Benchmark Trilean pick()
        $start = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $result = pick($condition, 'yes', 'no');
        }
        $trileanTime = microtime(true) - $start;

        $overheadPerOp = (($trileanTime - $nativeTime) / self::ITERATIONS) * 1000000;

        echo "\n";
        echo "Ternary Operator Benchmark (" . number_format(self::ITERATIONS) . " iterations):\n";
        echo "  Native PHP:       " . $this->formatTime($nativeTime) . " total\n";
        echo "  Trilean:          " . $this->formatTime($trileanTime) . " total\n";
        echo "  Overhead/op:      " . round($overheadPerOp, 3) . " μs\n";

        $this->assertLessThan(
            0.20, // 200ns per operation (pick() has function call + match expression)
            $overheadPerOp,
            "pick() adds {$overheadPerOp}μs per operation"
        );
    }

    /** @test */
    public function benchmark_conversion_speed()
    {
        $stringValue = 'true';

        // First conversion
        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $state = TernaryState::fromMixed($stringValue);
        }
        $timeFor1000 = microtime(true) - $start;
        $perOp = ($timeFor1000 / 1000) * 1000000;

        echo "\n";
        echo "String Conversion Benchmark (1,000 iterations):\n";
        echo "  Total time:       " . $this->formatTime($timeFor1000) . "\n";
        echo "  Per conversion:   " . round($perOp, 3) . " μs\n";

        // String conversions are inherently slower (normalization required)
        // But should still be under 2μs per conversion
        $this->assertLessThan(
            2.0,
            $perOp,
            "String conversion takes {$perOp}μs (should be < 2μs)"
        );
    }

    /** @test */
    public function benchmark_array_operations()
    {
        $values = array_fill(0, 100, true);

        // Benchmark native array_filter
        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $result = array_filter($values, fn($v) => $v === true);
        }
        $nativeTime = microtime(true) - $start;

        // Benchmark Trilean array_filter_true
        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $result = array_filter_true($values);
        }
        $trileanTime = microtime(true) - $start;

        $overheadPercent = (($trileanTime / $nativeTime) - 1) * 100;

        echo "\n";
        echo "Array Filter Benchmark (1,000 iterations, 100 items each):\n";
        echo "  Native PHP:       " . $this->formatTime($nativeTime) . "\n";
        echo "  Trilean:          " . $this->formatTime($trileanTime) . "\n";
        echo "  Overhead:         " . round($overheadPercent, 2) . "%\n";

        // Array operations should have minimal overhead (< 10%)
        $this->assertLessThan(
            10.0,
            $overheadPercent,
            "array_filter_true() has {$overheadPercent}% overhead (should be < 10%)"
        );
    }

    /** @test */
    public function benchmark_real_world_scenario()
    {
        // Simulate real-world validation scenario
        $user = (object) [
            'verified' => true,
            'consent' => true,
            'active' => true,
            'age' => 25,
        ];

        // Native PHP validation
        $start = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $canProceed = $user->verified === true
                && $user->consent === true
                && $user->active === true
                && $user->age >= 18;
        }
        $nativeTime = microtime(true) - $start;

        // Trilean validation
        $start = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $canProceed = and_all(
                $user->verified,
                $user->consent,
                $user->active,
                $user->age >= 18
            );
        }
        $trileanTime = microtime(true) - $start;

        $overheadPerOp = (($trileanTime - $nativeTime) / self::ITERATIONS) * 1000000;

        echo "\n";
        echo "Real-World Validation Benchmark (" . number_format(self::ITERATIONS) . " iterations):\n";
        echo "  Native PHP:       " . $this->formatTime($nativeTime) . " total\n";
        echo "  Trilean:          " . $this->formatTime($trileanTime) . " total\n";
        echo "  Overhead/op:      " . round($overheadPerOp, 3) . " μs\n";
        echo "\n";
        echo "  💡 Real-world impact: For 1 million requests/day:\n";
        echo "     Extra time = " . round($overheadPerOp * 1000000 / 1000, 2) . " ms/day\n";
        echo "     = " . round($overheadPerOp * 1000000 / 1000000, 2) . " seconds/day\n";

        // Real-world validation should add < 0.1μs per operation
        $this->assertLessThan(
            0.1,
            $overheadPerOp,
            "Real-world scenario adds {$overheadPerOp}μs per operation (should be < 0.1μs)"
        );
    }

    private function formatTime(float $seconds): string
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000, 2) . ' μs';
        }
        if ($seconds < 1) {
            return round($seconds * 1000, 2) . ' ms';
        }
        return round($seconds, 2) . ' s';
    }
}
