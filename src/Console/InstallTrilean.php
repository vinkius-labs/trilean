<?php

namespace VinkiusLabs\Trilean\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class InstallTrilean extends Command
{
    protected $signature = 'trilean:install {preset=laravel : Preset name (laravel, lumen, octane)} {--force : Overwrite existing files} {--playground : Copy demo playground}';

    protected $description = 'Publishes configuration, resources, and stubs for Trilean and applies the selected preset.';

    public function handle(): int
    {
        $preset = $this->argument('preset');
        $force = (bool) $this->option('force');
        $playground = (bool) $this->option('playground');

        if (! $this->validatePreset($preset)) {
            return static::FAILURE;
        }

        $this->publishAssets($force);
        $this->applyPreset($preset, $force);

        if ($playground) {
            $this->publishPlayground(app(Filesystem::class), $force);
        }

        $this->displaySuccessMessage();

        return static::SUCCESS;
    }

    private function validatePreset(string $preset): bool
    {
        $presets = config('trilean.presets', []);

        if (! isset($presets[$preset])) {
            $this->error("Preset [{$preset}] is not configured. Available: " . implode(', ', array_keys($presets)));

            return false;
        }

        return true;
    }

    private function publishAssets(bool $force): void
    {
        $this->line('> Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'trilean-config',
            '--force' => $force,
        ]);

        $this->line('> Publishing shared resources...');
        $this->call('vendor:publish', [
            '--tag' => 'trilean-resources',
            '--force' => $force,
        ]);
    }

    private function applyPreset(string $preset, bool $force): void
    {
        $presets = config('trilean.presets', []);
        $resources = Collection::make($presets[$preset]['resources'] ?? []);
        $filesystem = app(Filesystem::class);

        $this->line('> Applying preset: ' . $preset);

        $resources->each(function (string $destination, string $source) use ($filesystem, $force) {
            $target = base_path($destination);

            if ($filesystem->exists($target) && ! $force) {
                $this->warn(" - Skipped (already exists): {$target}");

                return;
            }

            $filesystem->ensureDirectoryExists(dirname($target));
            $filesystem->copy($source, $target);
            $this->info(" - Copied: {$destination}");
        });
    }

    private function displaySuccessMessage(): void
    {
        $this->newLine();
        $this->info('Trilean installed successfully 🎉');
        $this->comment('Run npm install && npm run dev to compile TypeScript assets when applicable.');
    }

    private function publishPlayground(Filesystem $filesystem, bool $force): void
    {
        $this->line('> Publishing playground...');

        $playground = config('trilean.playground', []);

        foreach ($playground as $source => $relativeTarget) {
            $target = base_path(trim($relativeTarget, '/'));

            if (is_dir($source)) {
                if ($filesystem->exists($target) && $force) {
                    $filesystem->deleteDirectory($target);
                }

                if ($filesystem->exists($target) && ! $force) {
                    $this->warn(" - Skipped (already exists): {$relativeTarget}");

                    continue;
                }

                $filesystem->copyDirectory($source, $target);
                $this->info(" - Directory copied: {$relativeTarget}");

                continue;
            }

            if ($filesystem->exists($target) && ! $force) {
                $this->warn(" - Skipped (already exists): {$relativeTarget}");

                continue;
            }

            $filesystem->ensureDirectoryExists(dirname($target));
            $filesystem->copy($source, $target);
            $this->info(" - File copied: {$relativeTarget}");
        }
    }
}
