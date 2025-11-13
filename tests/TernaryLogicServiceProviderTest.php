<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;
use VinkiusLabs\Trilean\Support\BalancedTernaryConverter;
use VinkiusLabs\Trilean\Services\TernaryExpressionEvaluator;
use VinkiusLabs\Trilean\Services\TernaryLogicService;
use VinkiusLabs\Trilean\Decision\TernaryDecisionEngine;
use VinkiusLabs\Trilean\Support\TernaryArithmetic;

class TernaryLogicServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'trilean.logic' => TernaryLogicService::class,
            'trilean.decision' => TernaryDecisionEngine::class,
        ];
    }

    public function test_services_are_registered()
    {
        $this->assertInstanceOf(BalancedTernaryConverter::class, $this->app->make(BalancedTernaryConverter::class));
        $this->assertInstanceOf(TernaryExpressionEvaluator::class, $this->app->make(TernaryExpressionEvaluator::class));
        $this->assertInstanceOf(TernaryLogicService::class, $this->app->make(TernaryLogicService::class));
        $this->assertInstanceOf(TernaryDecisionEngine::class, $this->app->make(TernaryDecisionEngine::class));
        $this->assertInstanceOf(TernaryArithmetic::class, $this->app->make(TernaryArithmetic::class));
    }

    public function test_aliases_are_registered()
    {
        $this->assertInstanceOf(TernaryLogicService::class, $this->app->make('trilean.logic'));
        $this->assertInstanceOf(TernaryDecisionEngine::class, $this->app->make('trilean.decision'));
    }

    public function test_config_is_merged()
    {
        $this->assertNotNull(config('trilean'));
    }

    public function test_publishing_works_in_console()
    {
        // Mock console environment
        $this->app['env'] = 'testing';
        $this->app->bind('runningInConsole', fn() => true);

        // Test that publishing commands are available
        $commands = Artisan::all();
        $this->assertArrayHasKey('trilean:install', $commands);
        $this->assertArrayHasKey('trilean:doctor', $commands);
    }

    public function test_publishing_resources_with_presets()
    {
        // Set up config with presets
        config(['trilean.presets' => [
            [
                'resources' => [
                    'source1' => 'dest1',
                    'source2' => 'dest2',
                ]
            ]
        ]]);

        $this->app->bind('runningInConsole', fn() => true);

        // The provider should register publishes, but testing actual publishing requires more setup
        // For now, just ensure no exceptions are thrown during boot
        $provider = new TernaryLogicServiceProvider($this->app);
        $provider->boot();

        $this->assertTrue(true); // If we reach here, no exceptions
    }

    public function test_publishing_playground()
    {
        // Set up config with playground
        config(['trilean.playground' => [
            'play1' => 'playdest1',
        ]]);

        $this->app->bind('runningInConsole', fn() => true);

        $provider = new TernaryLogicServiceProvider($this->app);
        $provider->boot();

        $this->assertTrue(true);
    }
}