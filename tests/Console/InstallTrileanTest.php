<?php

namespace VinkiusLabs\Trilean\Tests\Console;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Console\InstallTrilean;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class InstallTrileanTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock config for presets
        config([
            'trilean.presets' => [
                'laravel' => [
                    'resources' => [
                        'stubs/laravel/model.php' => 'app/Models/Example.php',
                    ]
                ]
            ],
            'trilean.playground' => [
                'resources/playground/demo.php' => 'app/demo.php',
            ]
        ]);
    }

    public function test_command_fails_with_invalid_preset()
    {
        $this->artisan('trilean:install', ['preset' => 'invalid'])
            ->assertExitCode(1);
    }

    public function test_command_succeeds_with_valid_preset()
    {
        // Mock filesystem to avoid actual file operations
        $filesystem = $this->mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->andReturn(false);
        $filesystem->shouldReceive('ensureDirectoryExists');
        $filesystem->shouldReceive('copy');

        $this->app->instance(Filesystem::class, $filesystem);

        $this->artisan('trilean:install', ['preset' => 'laravel'])
            ->expectsOutput('> Publishing configuration...')
            ->expectsOutput('> Publishing shared resources...')
            ->expectsOutput('> Applying preset: laravel')
            ->expectsOutput('Trilean installed successfully 🎉')
            ->assertExitCode(0);
    }

    public function test_command_with_playground_option()
    {
        $filesystem = $this->mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->andReturn(false);
        $filesystem->shouldReceive('ensureDirectoryExists');
        $filesystem->shouldReceive('copy');
        $filesystem->shouldReceive('copyDirectory');

        $this->app->instance(Filesystem::class, $filesystem);

        $this->artisan('trilean:install', ['preset' => 'laravel', '--playground' => true])
            ->expectsOutput('> Publishing playground...')
            ->expectsOutput('Trilean installed successfully 🎉')
            ->assertExitCode(0);
    }

    public function test_command_skips_existing_files_without_force()
    {
        $filesystem = $this->mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->andReturn(true); // File exists
        $filesystem->shouldNotReceive('copy'); // Should not copy

        $this->app->instance(Filesystem::class, $filesystem);

        $this->artisan('trilean:install', ['preset' => 'laravel'])
            ->assertExitCode(0);
    }

    public function test_command_overwrites_with_force_option()
    {
        $filesystem = $this->mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->andReturn(true);
        $filesystem->shouldReceive('ensureDirectoryExists');
        $filesystem->shouldReceive('copy'); // Should copy even if exists

        $this->app->instance(Filesystem::class, $filesystem);

        $this->artisan('trilean:install', ['preset' => 'laravel', '--force' => true])
            ->assertExitCode(0);
    }
}