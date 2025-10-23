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
    protected $signature = 'trilean:doctor {--json : Retorna o diagnóstico em JSON}';

    protected $description = 'Executa verificações de saúde da instalação do Trilean.';

    public function handle(): int
    {
        $checks = collect([
            'service_provider' => [
                'label' => 'Service Provider registrado',
                'status' => app()->getProvider('VinkiusLabs\\Trilean\\TernaryLogicServiceProvider') !== null,
            ],
            'helpers' => [
                'label' => 'Helpers globais disponíveis',
                'status' => function_exists('trilean') && function_exists('ternary'),
            ],
            'collection_macros' => [
                'label' => 'Collection macros registradas',
                'status' => SupportCollection::hasMacro('ternaryConsensus'),
            ],
            'request_macros' => [
                'label' => 'Request macros registradas',
                'status' => Request::hasMacro('ternary'),
            ],
            'gate_macros' => [
                'label' => 'Gate macros registradas',
                'status' => method_exists(Gate::getFacadeRoot(), 'hasMacro') ? Gate::hasMacro('defineTernary') : false,
            ],
            'form_request' => [
                'label' => 'Classe base TernaryFormRequest acessível',
                'status' => class_exists('VinkiusLabs\\Trilean\\Support\\FormRequests\\TernaryFormRequest'),
            ],
            'config_published' => [
                'label' => 'Configuração publicada',
                'status' => file_exists(base_path('config/trilean.php')),
            ],
            'metrics_enabled' => [
                'label' => 'Métricas habilitadas',
                'status' => (bool) config('trilean.metrics.enabled'),
            ],
        ]);

        if ($this->option('json')) {
            $this->line($checks->map(fn($check) => ['label' => $check['label'], 'status' => $check['status']])->values()->toJson());

            return static::SUCCESS;
        }

        $this->table(['Verificação', 'Status'], $checks->map(function ($check) {
            return [$check['label'], $check['status'] ? '✅' : '❌'];
        }));

        $metrics = collect(config('trilean.metrics.drivers', []))->map(function ($driver, $name) {
            return [
                Str::headline($name),
                ! empty($driver['enabled']) ? 'habilitado' : 'desabilitado',
            ];
        });

        if ($metrics->isNotEmpty()) {
            $this->line('Drivers de métricas:');
            $this->table(['Driver', 'Estado'], $metrics->toArray());
        }

        $this->newLine();
        $this->info('Para inspetores adicionais use php artisan inspire ou php artisan list | grep trilean');

        return static::SUCCESS;
    }
}
