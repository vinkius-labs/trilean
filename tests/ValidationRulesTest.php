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

    public function test_ternary_validation_passes_for_supported_values(): void
    {
        $validator = Validator::make([
            'consent' => 'yes',
        ], [
            'consent' => 'ternary',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_gate_and_expression_rules(): void
    {
        $data = [
            'consent' => true,
            'verified' => true,
            'blocked' => false,
        ];

        $validator = Validator::make($data, [
            'eligibility' => 'ternary_gate:consent,verified,and',
            'confidence' => 'ternary_expression:consent AND verified AND !blocked',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_weighted_and_any_rules(): void
    {
        $data = [
            'email' => true,
            'sms' => false,
            'push' => true,
        ];

        $validator = Validator::make($data, [
            'notification' => 'ternary_any_true:email,sms,push',
            'decision' => 'ternary_weighted:email:2,sms:1,push:2',
        ]);

        $this->assertFalse($validator->fails());
    }
}
