<?php

namespace VinkiusLabs\Trilean\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use VinkiusLabs\Trilean\Decision\TernaryDecisionReport;

class TernaryDecisionEvaluated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly TernaryDecisionReport $report,
        public readonly array $context = [],
        public readonly array $blueprint = [],
    ) {}
}
