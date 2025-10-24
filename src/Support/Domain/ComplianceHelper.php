<?php

namespace VinkiusLabs\Trilean\Support\Domain;

use VinkiusLabs\Trilean\Services\TernaryLogicService;

/**
 * Compliance checker helper.
 * 
 * @example compliant($legal, $finance, $security)->strict()
 * @example approved($dept1, $dept2, $dept3)->requireAll()
 */
class ComplianceHelper
{
    private array $checks;
    private TernaryLogicService $logic;

    public function __construct(mixed ...$checks)
    {
        $this->checks = $checks;
        $this->logic = app(TernaryLogicService::class);
    }

    /**
     * Strict compliance - all checks must pass (be TRUE).
     */
    public function strict(): bool
    {
        return and_all(...$this->checks);
    }

    /**
     * Require all checks to pass (alias for strict).
     */
    public function requireAll(): bool
    {
        return $this->strict();
    }

    /**
     * Lenient compliance - at least one check must pass.
     */
    public function lenient(): bool
    {
        return or_any(...$this->checks);
    }

    /**
     * Require any check to pass (alias for lenient).
     */
    public function requireAny(): bool
    {
        return $this->lenient();
    }

    /**
     * Majority compliance - more than 50% must pass.
     */
    public function majority(): bool
    {
        $consensus = $this->logic->consensus($this->checks);
        return $consensus->isTrue();
    }

    /**
     * Weighted compliance with custom weights.
     */
    public function weighted(array $weights): bool
    {
        $result = $this->logic->weighted($this->checks, $weights);
        return $result->isTrue();
    }

    /**
     * Get compliance status summary.
     */
    public function status(): array
    {
        $passed = 0;
        $failed = 0;
        $pending = 0;

        foreach ($this->checks as $check) {
            if (is_true($check)) {
                $passed++;
            } elseif (is_false($check)) {
                $failed++;
            } else {
                $pending++;
            }
        }

        return [
            'passed' => $passed,
            'failed' => $failed,
            'pending' => $pending,
            'total' => count($this->checks),
            'compliant' => $failed === 0 && $pending === 0,
        ];
    }
}
