<?php

namespace VinkiusLabs\Trilean\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class InstallTrilean extends Command
{
    protected $signature = 'trilean:install {preset=laravel : Nome do preset (laravel, lumen, octane)} {--force : Sobrescrever arquivos existentes} {--playground : Copiar playground de demonstração}';

    protected $description = 'Publica configuração, recursos e stubs do Trilean e aplica o preset escolhido.';

    public function handle(): int
    {
        $preset = $this->argument('preset');
        $force = (bool) $this->option('force');
        $playground = (bool) $this->option('playground');

        $presets = config('trilean.presets', []);

        if (! isset($presets[$preset])) {
            $this->error("Preset [{$preset}] não está configurado. Disponíveis: " . implode(', ', array_keys($presets)));

            return static::FAILURE;
        }

        $this->line('> Publicando configuração...');
        $this->call('vendor:publish', [
            '--tag' => 'trilean-config',
            '--force' => $force,
        ]);

        $this->line('> Publicando recursos compartilhados...');
        $this->call('vendor:publish', [
            '--tag' => 'trilean-resources',
            '--force' => $force,
        ]);

        $resources = Collection::make($presets[$preset]['resources'] ?? []);
        $filesystem = app(Filesystem::class);

        $this->line('> Aplicando preset: ' . $preset);

        $resources->each(function (string $destination, string $source) use ($filesystem, $force) {
            $target = base_path($destination);

            if ($filesystem->exists($target) && ! $force) {
                $this->warn(" - Ignorado (já existe): {$target}");

                return;
            }

            $filesystem->ensureDirectoryExists(dirname($target));
            $filesystem->copy($source, $target);
            $this->info(" - Copiado: {$destination}");
        });

        if ($playground) {
            $this->publishPlayground($filesystem, $force);
        }

        $this->newLine();
        $this->info('Trilean instalado com sucesso 🎉');
        $this->comment('Execute npm install && npm run dev para compilar assets TypeScript, se aplicável.');

        return static::SUCCESS;
    }

    private function publishPlayground(Filesystem $filesystem, bool $force): void
    {
        $this->line('> Publicando playground...');

        $playground = config('trilean.playground', []);

        foreach ($playground as $source => $relativeTarget) {
            $target = base_path(trim($relativeTarget, '/'));

            if (is_dir($source)) {
                if ($filesystem->exists($target) && $force) {
                    $filesystem->deleteDirectory($target);
                }

                if ($filesystem->exists($target) && ! $force) {
                    $this->warn(" - Ignorado (já existe): {$relativeTarget}");

                    continue;
                }

                $filesystem->copyDirectory($source, $target);
                $this->info(" - Diretório copiado: {$relativeTarget}");

                continue;
            }

            if ($filesystem->exists($target) && ! $force) {
                $this->warn(" - Ignorado (já existe): {$relativeTarget}");

                continue;
            }

            $filesystem->ensureDirectoryExists(dirname($target));
            $filesystem->copy($source, $target);
            $this->info(" - Arquivo copiado: {$relativeTarget}");
        }
    }
}
