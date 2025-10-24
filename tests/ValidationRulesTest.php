<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class ValidationRulesTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_must_be_true_validation_rule()
    {
        $validator = Validator::make(['consent' => 'yes'], ['consent' => 'must_be_true']);
        $this->assertFalse($validator->fails());
        
        $validator = Validator::make(['consent' => 'no'], ['consent' => 'must_be_true']);
        $this->assertTrue($validator->fails());
    }

    public function test_cannot_be_false_validation_rule()
    {
        $validator = Validator::make(['blocked' => 'yes'], ['blocked' => 'cannot_be_false']);
        $this->assertFalse($validator->fails());
        
        $validator = Validator::make(['blocked' => 'no'], ['blocked' => 'cannot_be_false']);
        $this->assertTrue($validator->fails());
    }

    public function test_must_be_known_validation_rule()
    {
        $validator = Validator::make(['status' => 'yes'], ['status' => 'must_be_known']);
        $this->assertFalse($validator->fails());
        
        $validator = Validator::make(['status' => null], ['status' => 'must_be_known']);
        $this->assertTrue($validator->fails());
    }

    public function test_all_must_be_true_validation_rule()
    {
        $validator = Validator::make(
            ['checks' => [true, 'yes', 1]],
            ['checks' => 'all_must_be_true']
        );
        $this->assertFalse($validator->fails());
        
        $validator = Validator::make(
            ['checks' => [true, false, true]],
            ['checks' => 'all_must_be_true']
        );
        $this->assertTrue($validator->fails());
    }

    public function test_any_must_be_true_validation_rule()
    {
        $validator = Validator::make(
            ['methods' => [false, true, false]],
            ['methods' => 'any_must_be_true']
        );
        $this->assertFalse($validator->fails());
        
        $validator = Validator::make(
            ['methods' => [false, false, false]],
            ['methods' => 'any_must_be_true']
        );
        $this->assertTrue($validator->fails());
    }

    public function test_majority_true_validation_rule()
    {
        $validator = Validator::make(
            ['votes' => [true, true, false]],
            ['votes' => 'majority_true']
        );
        $this->assertFalse($validator->fails());
        
        $validator = Validator::make(
            ['votes' => [false, false, true]],
            ['votes' => 'majority_true']
        );
        $this->assertTrue($validator->fails());
    }

    public function test_true_if_validation_rule()
    {
        $validator = Validator::make(
            ['consent' => true, 'age' => 18],
            ['consent' => 'true_if:age,18']
        );
        $this->assertFalse($validator->fails());
        
        $validator = Validator::make(
            ['consent' => false, 'age' => 18],
            ['consent' => 'true_if:age,18']
        );
        $this->assertTrue($validator->fails());
    }

    public function test_false_if_validation_rule()
    {
        $validator = Validator::make(
            ['blocked' => false, 'status' => 'banned'],
            ['blocked' => 'false_if:status,banned']
        );
        $this->assertFalse($validator->fails());
        
        $validator = Validator::make(
            ['blocked' => true, 'status' => 'banned'],
            ['blocked' => 'false_if:status,banned']
        );
        $this->assertTrue($validator->fails());
    }
}
