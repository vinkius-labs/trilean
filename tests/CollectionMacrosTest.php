<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class CollectionMacrosTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_all_true_macro_works()
    {
        $collection = collect([true, 'yes', 1]);
        $this->assertTrue($collection->allTrue());
        
        $collection = collect([true, false, true]);
        $this->assertFalse($collection->allTrue());
    }

    public function test_any_true_macro_works()
    {
        $collection = collect([false, true, false]);
        $this->assertTrue($collection->anyTrue());
        
        $collection = collect([false, false, false]);
        $this->assertFalse($collection->anyTrue());
    }

    public function test_only_true_macro_filters_correctly()
    {
        $collection = collect([true, false, 'yes', 'no', null]);
        $filtered = $collection->onlyTrue();
        
        $this->assertCount(2, $filtered);
    }

    public function test_only_false_macro_filters_correctly()
    {
        $collection = collect([true, false, 'yes', 'no', null]);
        $filtered = $collection->onlyFalse();
        
        $this->assertCount(2, $filtered);
    }

    public function test_only_unknown_macro_filters_correctly()
    {
        $collection = collect([true, false, null, 'pending', 'yes']);
        $filtered = $collection->onlyUnknown();
        
        $this->assertCount(2, $filtered);
    }

    public function test_to_booleans_macro_converts_collection()
    {
        $collection = collect([true, false, null]);
        $booleans = $collection->toBooleans(false);
        
        $this->assertTrue($booleans[0]);
        $this->assertFalse($booleans[1]);
        $this->assertFalse($booleans[2]); // null -> false
        
        $booleansTrue = $collection->toBooleans(true);
        $this->assertTrue($booleansTrue[2]); // null -> true
    }

    public function test_vote_macro_works()
    {
        $collection = collect([true, true, false]);
        $result = $collection->vote();
        
        $this->assertEquals('true', $result);
        
        $collection = collect([true, false]);
        $result = $collection->vote();
        
        $this->assertEquals('tie', $result);
    }

    public function test_count_true_macro_works()
    {
        $collection = collect([true, false, 'yes', 'no', null]);
        $count = $collection->countTrue();
        
        $this->assertEquals(2, $count);
    }

    public function test_count_false_macro_works()
    {
        $collection = collect([true, false, 'yes', 'no', null]);
        $count = $collection->countFalse();
        
        $this->assertEquals(2, $count);
    }

    public function test_count_unknown_macro_works()
    {
        $collection = collect([true, false, null, 'pending']);
        $count = $collection->countUnknown();
        
        $this->assertEquals(2, $count);
    }
}
