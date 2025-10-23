<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class CollectionMacrosTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_ternary_consensus_macro()
    {
        $collection = collect([true, true, true]);
        $this->assertSame(TernaryState::TRUE, $collection->ternaryConsensus());

        $mixed = collect([true, false, null]);
        $this->assertSame(TernaryState::UNKNOWN, $mixed->ternaryConsensus());
    }

    public function test_ternary_majority_macro()
    {
        $collection = collect([true, true, false]);
        $this->assertSame(TernaryState::TRUE, $collection->ternaryMajority());
    }

    public function test_where_ternary_true_macro()
    {
        $collection = collect([
            ['verified' => true],
            ['verified' => false],
            ['verified' => null],
        ]);

        $filtered = $collection->whereTernaryTrue('verified');
        $this->assertCount(1, $filtered);
    }

    public function test_ternary_score_macro()
    {
        $collection = collect([true, false, null, true]);
        $score = $collection->ternaryScore();

        // true=+1, false=-1, null=0, true=+1 = 1
        $this->assertSame(1, $score);
    }

    public function test_all_ternary_true_macro()
    {
        $allTrue = collect([true, 1, 'yes']);
        $this->assertTrue($allTrue->allTernaryTrue());

        $mixed = collect([true, false, true]);
        $this->assertFalse($mixed->allTernaryTrue());
    }

    public function test_any_ternary_true_macro()
    {
        $collection = collect([false, null, true]);
        $this->assertTrue($collection->anyTernaryTrue());

        $allFalse = collect([false, 0, 'no']);
        $this->assertFalse($allFalse->anyTernaryTrue());
    }

    public function test_partition_ternary_macro()
    {
        $collection = collect([
            ['status' => true],
            ['status' => false],
            ['status' => null],
            ['status' => true],
        ]);

        [$trueItems, $falseItems, $unknownItems] = $collection->partitionTernary('status');

        $this->assertCount(2, $trueItems);
        $this->assertCount(1, $falseItems);
        $this->assertCount(1, $unknownItems);
    }

    public function test_ternary_gate_macro()
    {
        $andGate = collect([true, true, true]);
        $this->assertSame(TernaryState::TRUE, $andGate->ternaryGate('and'));

        $orGate = collect([false, false, true]);
        $this->assertSame(TernaryState::TRUE, $orGate->ternaryGate('or'));
    }

    public function test_additional_macros_cover_weighted_and_map()
    {
        $collection = collect([true, false, true]);
        $weighted = $collection->ternaryWeighted([3, -2, 1]);
        $this->assertSame(TernaryState::TRUE, $weighted);

        $mapped = $collection->ternaryMap(fn($value) => $value)->consensus();
        $this->assertSame(TernaryState::TRUE, $mapped);

        $xorGate = collect([true, false, false])->ternaryGate('xor');
        $this->assertSame(TernaryState::UNKNOWN, $xorGate);
    }
}
