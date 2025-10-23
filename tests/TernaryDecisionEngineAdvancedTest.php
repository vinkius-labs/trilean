<?php

namespace VinkiusLabs\Trilean\Tests;

use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Decision\TernaryDecision;
use VinkiusLabs\Trilean\Decision\TernaryDecisionEngine;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class TernaryDecisionEngineAdvancedTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_it_handles_callable_operands_and_expressions(): void
    {
        $engine = $this->app->make(TernaryDecisionEngine::class);

        $report = $engine->evaluate([
            'name' => 'eligibility_pipeline',
            'inputs' => [
                'score' => fn(array $context) => $context['score'] ?? null,
                'risk' => 'user.risk',
            ],
            'gates' => [
                'initial' => [
                    'operator' => 'or',
                    'operands' => [
                        'score',
                        function ($inputs) {
                            return $inputs->get('score');
                        },
                    ],
                ],
                'final' => [
                    'operator' => 'consensus',
                    'operands' => [
                        'initial',
                        '!risk',
                        '@score AND !risk',
                    ],
                    'description' => 'Consensus decision blending inverted risk.',
                ],
            ],
            'output' => 'final',
        ], [
            'score' => true,
            'user' => ['risk' => false],
        ]);

        $this->assertTrue($report->result()->isTrue());
        $this->assertCount(2, $report->decisions());
        $this->assertNotEmpty($report->encodedVector());
        $this->assertSame('eligibility_pipeline', $report->metadata()['blueprint']);

        $decisionNames = $report->decisions()->map(fn(TernaryDecision $decision) => $decision->name)->all();
        $this->assertSame(['initial', 'final'], $decisionNames);
    }

    public function test_expression_operator_merges_context(): void
    {
        $engine = $this->app->make(TernaryDecisionEngine::class);

        $report = $engine->evaluate([
            'inputs' => [
                'feature' => 'feature.enabled',
            ],
            'gates' => [
                'dynamic' => [
                    'operator' => 'expression',
                    'expression' => 'feature AND !risk',
                ],
            ],
            'output' => 'dynamic',
        ], [
            'feature' => ['enabled' => 'true'],
            'risk' => false,
        ]);

        $this->assertTrue($report->result()->isTrue());
        $this->assertSame('true', $report->toArray()['result']);
    }
}
