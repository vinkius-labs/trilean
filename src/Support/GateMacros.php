<?php

namespace VinkiusLabs\Trilean\Support;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use VinkiusLabs\Trilean\Support\Gate\MacroableGate;

class GateMacros
{
    public static function register(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = App::getFacadeApplication();

        $gate = $app->make(GateContract::class);

        if (! $gate instanceof MacroableGate) {
            $macroableGate = MacroableGate::fromGate($gate);
            $app->instance(GateContract::class, $macroableGate);
            $app->instance('gate', $macroableGate);
            Gate::clearResolvedInstance(GateContract::class);
            Gate::clearResolvedInstance('gate');
        }
    }
}
