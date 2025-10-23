<?php

use Illuminate\Support\Facades\App;
use VinkiusLabs\Trilean\Services\TernaryLogicService;
use function ternary_match;

$logic = App::make(TernaryLogicService::class);

$signals = [true, 'unknown', false];
$decision = $logic->consensus($signals);

return [
    'decision' => $decision->value,
    'encoded' => $logic->encode($signals),
    'label' => ternary_match($decision, [
        'true' => 'Aprovado',
        'false' => 'Rejeitado',
        'unknown' => 'Revisar',
    ]),
];
