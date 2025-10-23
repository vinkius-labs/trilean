<?php

return [
    'ui' => [
        'badges' => [
            'true' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            'false' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            'unknown' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        ],
        'icons' => [
            'true' => 'heroicon-o-check-circle',
            'false' => 'heroicon-o-x-circle',
            'unknown' => 'heroicon-o-exclamation-circle',
        ],
    ],

    'policies' => [
        'unknown_resolves_to' => false,
        'throw_on_unknown' => false,
        'unknown_message' => 'Esta decisão ainda está pendente.',
    ],

    'metrics' => [
        'enabled' => env('TRILEAN_METRICS', true),
        'default_tags' => [
            'app' => env('APP_NAME', 'laravel'),
        ],
        'drivers' => [
            'log' => [
                'channel' => env('TRILEAN_METRICS_LOG_CHANNEL', 'stack'),
            ],
            'telescope' => [
                'enabled' => env('TRILEAN_TELESCOPE_EXPORT', true),
            ],
            'horizon' => [
                'enabled' => env('TRILEAN_HORIZON_EXPORT', true),
                'connection' => env('HORIZON_REDIS_CONNECTION', 'horizon'),
                'prefix' => env('HORIZON_PREFIX', 'horizon:'),
            ],
            'prometheus' => [
                'enabled' => env('TRILEAN_PROMETHEUS_EXPORT', true),
                'namespace' => env('TRILEAN_PROMETHEUS_NAMESPACE', 'trilean'),
                'collector' => env('TRILEAN_PROMETHEUS_COLLECTOR', null),
            ],
        ],
    ],

    'presets' => [
        'laravel' => [
            'description' => 'Preset padrão para aplicações Laravel full stack.',
            'resources' => [
                __DIR__ . '/../resources/stubs/feature-flag.stub' => 'stubs/trilean/feature-flag.stub',
                __DIR__ . '/../resources/stubs/compliance.stub' => 'stubs/trilean/compliance.stub',
                __DIR__ . '/../resources/livewire/TernaryBadge.php.stub' => 'app/Livewire/TernaryBadge.php',
                __DIR__ . '/../resources/views/components/trilean/badge.blade.php.stub' => 'resources/views/components/trilean/badge.blade.php',
                __DIR__ . '/../resources/inertia/TernaryBadge.vue.stub' => 'resources/js/Components/TernaryBadge.vue',
                __DIR__ . '/../resources/typescript/trilean.ts' => 'resources/js/trilean.ts',
            ],
        ],
        'lumen' => [
            'description' => 'Preset enxuto para microserviços Lumen.',
            'resources' => [
                __DIR__ . '/../resources/stubs/feature-flag.stub' => 'stubs/trilean/feature-flag.stub',
            ],
        ],
        'octane' => [
            'description' => 'Preset otimizado para servidores Octane.',
            'resources' => [
                __DIR__ . '/../resources/stubs/feature-flag.stub' => 'stubs/trilean/feature-flag.stub',
                __DIR__ . '/../resources/stubs/compliance.stub' => 'stubs/trilean/compliance.stub',
                __DIR__ . '/../resources/typescript/trilean.ts' => 'resources/js/trilean.ts',
            ],
        ],
    ],

    'playground' => [
        __DIR__ . '/../resources/playground/tinkerwell/trilean_snippet.php' => 'playground/tinkerwell/trilean_snippet.php',
        __DIR__ . '/../resources/playground/demo-app' => 'playground/demo-app',
    ],
];
