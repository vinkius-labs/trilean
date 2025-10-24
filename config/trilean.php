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
        'unknown_message' => 'This decision is still pending.',
    ],

    'cache' => [
        'enabled' => env('TRILEAN_CACHE_ENABLED', false),
        'ttl' => env('TRILEAN_CACHE_TTL', 3600), // seconds
        'driver' => env('TRILEAN_CACHE_DRIVER', 'memory'), // memory, redis, file
    ],

    'metrics' => [
        'enabled' => env('TRILEAN_METRICS', false),
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
                'enabled' => env('TRILEAN_HORIZON_EXPORT', false),
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
            'description' => 'Default preset for full-stack Laravel applications.',
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
            'description' => 'Lean preset for Lumen microservices.',
            'resources' => [
                __DIR__ . '/../resources/stubs/feature-flag.stub' => 'stubs/trilean/feature-flag.stub',
            ],
        ],
        'octane' => [
            'description' => 'Preset optimized for Octane workloads.',
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
