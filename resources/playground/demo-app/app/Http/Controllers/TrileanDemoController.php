<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VinkiusLabs\Trilean\Decision\TernaryDecisionEngine;
use VinkiusLabs\Trilean\Services\TernaryLogicService;

class TrileanDemoController extends Controller
{
    public function __invoke(Request $request, TernaryDecisionEngine $engine)
    {
        $blueprint = [
            'name' => 'demo-feature-flag',
            'inputs' => [
                'experiment' => fn() => ternary($request->input('experiment', 'unknown')),
                'rollout' => fn() => ternary($request->input('rollout', true)),
                'override' => 'feature.override',
            ],
            'gates' => [
                'eligibility' => [
                    'operator' => 'weighted',
                    'operands' => ['experiment', 'rollout', 'override'],
                    'weights' => [3, 2, 1],
                    'description' => 'Combina experimentos, rollout e override manual',
                ],
            ],
            'output' => 'eligibility',
        ];

        $context = [
            'feature.override' => ternary($request->input('override', 'unknown')),
        ];

        $report = $engine->evaluate($blueprint, $context);

        return view('trilean-demo', [
            'report' => $report,
            'encoded' => $report->encodedVector(),
            'state' => $report->result(),
        ]);
    }
}
