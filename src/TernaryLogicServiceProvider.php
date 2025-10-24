<?php

namespace VinkiusLabs\Trilean;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Support\ServiceProvider;
use VinkiusLabs\Trilean\Console\DoctorTrilean;
use VinkiusLabs\Trilean\Console\InstallTrilean;
use VinkiusLabs\Trilean\Decision\TernaryDecisionEngine;
use VinkiusLabs\Trilean\Macros\BuilderMacros;
use VinkiusLabs\Trilean\Macros\CollectionMacros;
use VinkiusLabs\Trilean\Macros\RequestMacros;
use VinkiusLabs\Trilean\Support\TernaryArithmetic;
use VinkiusLabs\Trilean\Services\TernaryExpressionEvaluator;
use VinkiusLabs\Trilean\Services\TernaryLogicService;
use VinkiusLabs\Trilean\Support\BalancedTernaryConverter;
use VinkiusLabs\Trilean\Support\Gate\MacroableGate;
use VinkiusLabs\Trilean\Support\GateMacros;
use VinkiusLabs\Trilean\Support\Metrics\TernaryMetrics;
use VinkiusLabs\Trilean\Validation\ValidationRules;
use VinkiusLabs\Trilean\View\BladeDirectives;

class TernaryLogicServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/trilean.php', 'trilean');

        $this->app->singleton(BalancedTernaryConverter::class);

        $this->app->singleton(TernaryExpressionEvaluator::class, fn() => new TernaryExpressionEvaluator());

        $this->app->singleton(TernaryLogicService::class, function ($app) {
            $service = new TernaryLogicService(
                converter: $app->make(BalancedTernaryConverter::class),
            );

            $service->setExpressionEvaluator($app->make(TernaryExpressionEvaluator::class));

            return $service;
        });

        $this->app->alias(TernaryLogicService::class, 'trilean.logic');

        $this->app->singleton(TernaryDecisionEngine::class, fn($app) => new TernaryDecisionEngine(
            logic: $app->make(TernaryLogicService::class)
        ));

        $this->app->alias(TernaryDecisionEngine::class, 'trilean.decision');

        $this->app->singleton(TernaryArithmetic::class, fn($app) => new TernaryArithmetic(
            converter: $app->make(BalancedTernaryConverter::class)
        ));

        $this->app->extend(GateContract::class, function ($gate, $app) {
            return $gate instanceof MacroableGate ? $gate : MacroableGate::fromGate($gate);
        });
    }

    public function boot(): void
    {
        // Load helper functions
        if (file_exists(__DIR__ . '/Helpers/functions.php')) {
            require_once __DIR__ . '/Helpers/functions.php';
        }

        // Register macros and directives
        CollectionMacros::register();
        RequestMacros::register();
        BuilderMacros::register();
        BladeDirectives::register();
        ValidationRules::register();

        GateMacros::register();
        TernaryMetrics::boot();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'trilean');

        $this->registerPublishing();
        $this->registerCommands();
    }

    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/trilean.php' => $this->app->configPath('trilean.php'),
        ], 'trilean-config');

        $resources = [];

        foreach (config('trilean.presets', []) as $preset) {
            foreach ($preset['resources'] ?? [] as $source => $destination) {
                $resources[$source] = $this->app->basePath($destination);
            }
        }

        if (! empty($resources)) {
            $this->publishes($resources, 'trilean-resources');
        }

        $playground = [];

        foreach (config('trilean.playground', []) as $source => $destination) {
            $playground[$source] = $this->app->basePath($destination);
        }

        if (! empty($playground)) {
            $this->publishes($playground, 'trilean-playground');
        }
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallTrilean::class,
            DoctorTrilean::class,
        ]);
    }
}
