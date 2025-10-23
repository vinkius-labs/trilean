<?php

namespace VinkiusLabs\Trilean\Tests;

use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Decision\TernaryDecisionEngine;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Services\TernaryLogicService;
use VinkiusLabs\Trilean\Support\BalancedTernaryConverter;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class TernaryLogicTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_basic_truth_table_constraints(): void
    {
        $logic = $this->logic();

        $this->assertSame(TernaryState::UNKNOWN, $logic->and(TernaryState::TRUE, TernaryState::UNKNOWN));
        $this->assertSame(TernaryState::UNKNOWN, $logic->or(TernaryState::FALSE, TernaryState::UNKNOWN));
        $this->assertSame(TernaryState::UNKNOWN, $logic->not(TernaryState::UNKNOWN));
    }

    public function test_weighted_decision_prioritises_negative_scores(): void
    {
        $logic = $this->logic();

        $state = $logic->weighted([
            TernaryState::TRUE,
            TernaryState::FALSE,
            TernaryState::UNKNOWN,
        ], [1, 2, 1]);

        $this->assertSame(TernaryState::FALSE, $state);
    }

    public function test_balanced_ternary_conversion_round_trip(): void
    {
        $converter = $this->app->make(BalancedTernaryConverter::class);

        $balanced = $converter->toBalanced(42);
        $this->assertSame(42, $converter->fromBalanced($balanced));
    }

    public function test_expression_evaluator_with_context(): void
    {
        $logic = $this->logic();

        $state = $logic->expression('consent AND !risk', [
            'consent' => 'true',
            'risk' => 'unknown',
        ]);

        $this->assertSame(TernaryState::UNKNOWN, $state);
    }

    public function test_decision_engine_generates_report_with_encoding(): void
    {
        $engine = $this->app->make(TernaryDecisionEngine::class);

        $report = $engine->evaluate([
            'inputs' => [
                'consent' => 'true',
                'risk' => 'user.risk',
            ],
            'gates' => [
                'eligibility' => [
                    'operator' => 'and',
                    'operands' => ['consent', '!risk'],
                    'description' => 'Eligibility blends consent with inverted risk.'
                ],
                'final' => [
                    'operator' => 'weighted',
                    'operands' => ['eligibility', 'consent', 'risk'],
                    'weights' => [3, 1, -2],
                ],
            ],
            'output' => 'final',
        ], [
            'user' => ['risk' => TernaryState::UNKNOWN],
        ]);

        $this->assertSame('unknown', $report->result()->value);
        $this->assertNotEmpty($report->encodedVector());
        $this->assertCount(2, $report->decisions());
    }

    public function test_new_operators_in_expressions(): void
    {
        $logic = $this->logic();

        // Test MAJ (majority)
        $state = $logic->expression('MAJ(true, false, unknown)', []);
        $this->assertSame(TernaryState::UNKNOWN, $state);

        $state = $logic->expression('MAJ(true, true, false)', []);
        $this->assertSame(TernaryState::UNKNOWN, $state);

        // Test CONSENSUS
        $state = $logic->expression('CONSENSUS(true, true, false)', []);
        $this->assertSame(TernaryState::UNKNOWN, $state);

        $state = $logic->expression('CONSENSUS(true, false, unknown)', []);
        $this->assertSame(TernaryState::UNKNOWN, $state);

        // Test IF
        $state = $logic->expression('IF(true, true, false)', []);
        $this->assertSame(TernaryState::UNKNOWN, $state);

        $state = $logic->expression('IF(false, true, false)', []);
        $this->assertSame(TernaryState::UNKNOWN, $state);

        $state = $logic->expression('IF(unknown, true, false)', []);
        $this->assertSame(TernaryState::UNKNOWN, $state);
    }

    public function test_large_vector_operations(): void
    {
        $logic = $this->logic();

        $largeTrue = array_fill(0, 100, TernaryState::TRUE);
        $this->assertSame(TernaryState::TRUE, $logic->and(...$largeTrue));

        $mixed = array_merge(array_fill(0, 50, TernaryState::TRUE), array_fill(0, 50, TernaryState::FALSE));
        $this->assertSame(TernaryState::FALSE, $logic->and(...$mixed));
        $this->assertSame(TernaryState::TRUE, $logic->or(...$mixed));
    }

    public function test_balanced_ternary_edge_cases(): void
    {
        $converter = $this->app->make(BalancedTernaryConverter::class);

        // Large number
        $large = 123456;
        $balanced = $converter->toBalanced($large);
        $this->assertSame($large, $converter->fromBalanced($balanced));

        // Negative
        $neg = -42;
        $balanced = $converter->toBalanced($neg);
        $this->assertSame($neg, $converter->fromBalanced($balanced));

        // Zero
        $this->assertSame('0', $converter->toBalanced(0));
        $this->assertSame(0, $converter->fromBalanced('0'));
    }

    public function test_decision_engine_error_handling(): void
    {
        $engine = $this->app->make(TernaryDecisionEngine::class);

        // Missing operands
        $this->expectException(\Exception::class);
        $engine->evaluate([
            'inputs' => [],
            'gates' => [
                'test' => [
                    'operator' => 'and',
                    'operands' => ['missing'],
                ],
            ],
        ]);
    }

    private function logic(): TernaryLogicService
    {
        return $this->app->make(TernaryLogicService::class);
    }
}
