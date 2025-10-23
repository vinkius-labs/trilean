<?php

namespace VinkiusLabs\Trilean\Support\Metrics;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Laravel\Telescope\Telescope;
use Prometheus\CollectorRegistry;
use Throwable;
use VinkiusLabs\Trilean\Decision\TernaryDecisionReport;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Events\TernaryDecisionEvaluated;
use function collect;

class TernaryMetrics
{
    public static function boot(): void
    {
        if (! config('trilean.metrics.enabled', false)) {
            return;
        }

        Event::listen(TernaryDecisionEvaluated::class, function (TernaryDecisionEvaluated $event): void {
            self::recordDecision($event->report, $event->context, $event->blueprint);
        });
    }

    public static function recordState(TernaryState $state, array $context = []): void
    {
        $report = new TernaryDecisionReport($state, collect(), $state->value, ['context' => $context]);

        self::recordDecision($report, $context, []);
    }

    public static function recordDecision(TernaryDecisionReport $report, array $context = [], array $blueprint = []): void
    {
        $drivers = config('trilean.metrics.drivers', []);
        $tags = array_merge(
            config('trilean.metrics.default_tags', []),
            Arr::only($context, ['tenant', 'feature', 'workflow']),
            array_filter([
                'blueprint' => $blueprint['name'] ?? $blueprint['id'] ?? null,
                'state' => $report->result()->value,
            ]),
        );

        if (! empty($drivers['log']['channel'])) {
            Log::channel($drivers['log']['channel'])->info('trilean.decision', [
                'state' => $report->result()->value,
                'metadata' => $report->metadata(),
                'decisions' => $report->decisions()->count(),
                'tags' => $tags,
            ]);
        }

        if (! empty($drivers['horizon']['enabled']) && class_exists(Redis::class)) {
            $connection = $drivers['horizon']['connection'] ?? 'horizon';
            $prefix = $drivers['horizon']['prefix'] ?? 'horizon:';
            $key = $prefix . 'metrics:trilean';

            $connections = config('database.redis', []);

            if (! array_key_exists($connection, $connections)) {
                Log::debug('Trilean metrics horizon driver skipped: redis connection missing.', [
                    'connection' => $connection,
                ]);
            } else {
                try {
                    Redis::connection($connection)->pipeline(function ($pipe) use ($key, $report): void {
                        $pipe->hincrby($key, 'count:' . $report->result()->value, 1);
                        $metadata = $report->metadata();
                        if (isset($metadata['duration_ms'])) {
                            $pipe->hincrbyfloat($key, 'duration:' . $report->result()->value, (float) $metadata['duration_ms']);
                        }
                    });
                } catch (Throwable $exception) {
                    Log::debug('Trilean metrics horizon driver skipped: redis operation failed.', [
                        'connection' => $connection,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            }
        }

        if (! empty($drivers['telescope']['enabled']) && class_exists(Telescope::class) && method_exists(Telescope::class, 'recordLog')) {
            Telescope::recordLog(
                'info',
                'Trilean decision: ' . $report->result()->value,
                [
                    'state' => $report->result()->value,
                    'metadata' => $report->metadata(),
                    'tags' => $tags,
                ]
            );
        }

        if (! empty($drivers['prometheus']['enabled']) && class_exists(CollectorRegistry::class) && app()->bound(CollectorRegistry::class)) {
            /** @var CollectorRegistry $registry */
            $registry = app(CollectorRegistry::class);
            $namespace = $drivers['prometheus']['namespace'] ?? 'trilean';

            $counter = $registry->getOrRegisterCounter(
                $namespace,
                'decisions_total',
                'Total ternary decisions emitted',
                array_keys($tags) ?: ['state']
            );

            $histogram = $registry->getOrRegisterHistogram(
                $namespace,
                'decision_duration_ms',
                'Duration of ternary decisions in ms',
                array_keys($tags) ?: ['state']
            );

            $labels = array_values($tags) ?: [$report->result()->value];
            $counter->inc($labels);

            $duration = (float) ($report->metadata()['duration_ms'] ?? 0.0);
            $histogram->observe($labels, $duration);
        }
    }
}
