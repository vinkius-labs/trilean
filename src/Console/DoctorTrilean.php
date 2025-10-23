<?php

namespace VinkiusLabs\Trilean\Console;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Collection as SupportCollection;
use VinkiusLabs\Trilean\Services\TernaryLogicService;
use function base_path;
use function class_exists;
use function collect;
use function config;
use function function_exists;

class DoctorTrilean extends Command
{
    protected $signature = 'trilean:doctor {--json : Returns the diagnostics as JSON}';

    protected $description = 'Runs health checks for the Trilean installation.';

    public function handle(): int
    {
        $checks = collect([
            'service_provider' => [
                'label' => 'Service provider registered',
                'status' => app()->getProvider('VinkiusLabs\\Trilean\\TernaryLogicServiceProvider') !== null,
            ],
            'helpers' => [
                'label' => 'Global helpers available',
                'status' => function_exists('trilean') && function_exists('ternary'),
            ],
            'collection_macros' => [
                'label' => 'Collection macros registered',
                'status' => SupportCollection::hasMacro('ternaryConsensus'),
            ],
            'request_macros' => [
                'label' => 'Request macros registered',
                'status' => Request::hasMacro('ternary'),
            ],
            'gate_macros' => [
                'label' => 'Gate macros registered',
                'status' => ($gate = Gate::getFacadeRoot()) && method_exists($gate, 'defineTernary'),
            ],
            'form_request' => [
                'label' => 'TernaryFormRequest base class available',
                'status' => class_exists('VinkiusLabs\\Trilean\\Support\\FormRequests\\TernaryFormRequest'),
            ],
            'config_published' => [
                'label' => 'Configuration published',
                'status' => file_exists(base_path('config/trilean.php')),
            ],
            'metrics_enabled' => [
                'label' => 'Metrics enabled',
                'status' => (bool) config('trilean.metrics.enabled'),
            ],
        ]);

        if ($this->option('json')) {
            $this->line($checks->map(fn($check) => ['label' => $check['label'], 'status' => $check['status']])->values()->toJson());

            return static::SUCCESS;
        }

        $this->table(['Check', 'Status'], $checks->map(function ($check) {
            return [$check['label'], $check['status'] ? '✅' : '❌'];
        }));

        $metrics = collect(config('trilean.metrics.drivers', []))->map(function ($driver, $name) {
            return [
                Str::headline($name),
                ! empty($driver['enabled']) ? 'enabled' : 'disabled',
            ];
        });

        if ($metrics->isNotEmpty()) {
            $this->line('Metrics drivers:');
            $this->table(['Driver', 'Status'], $metrics->toArray());
        }

        $this->newLine();
        $this->info('For additional inspectors run php artisan inspire or php artisan list | grep trilean');

        return static::SUCCESS;
    }
}
