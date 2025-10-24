<?php

namespace VinkiusLabs\Trilean\Support;

use VinkiusLabs\Trilean\Decision\TernaryDecisionEngine;
use VinkiusLabs\Trilean\Decision\TernaryDecisionReport;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Services\TernaryLogicService;

/**
 * Fluent builder for decision trees - simplified API for Decision Engine.
 * 
 * @example
 * $report = decide()
 *     ->input('verified', $user->verified)
 *     ->input('consent', $user->consent)
 *     ->and('compliance', ['verified', 'consent'])
 *     ->output('compliance')
 *     ->evaluate();
 * 
 * @example
 * $canProceed = decide($user->verified, $user->consent)
 *     ->requireAll()
 *     ->toBool();
 */
class DecisionBuilder
{
    private array $inputs = [];
    private array $gates = [];
    private ?string $output = null;
    private array $context = [];
    private ?string $name = null;

    public function __construct(
        private readonly TernaryDecisionEngine $engine,
        private readonly TernaryLogicService $logic,
        mixed ...$quickInputs
    ) {
        // Quick constructor for simple cases
        if (!empty($quickInputs)) {
            foreach ($quickInputs as $index => $input) {
                $this->inputs["input_{$index}"] = $input;
            }
        }
    }

    /**
     * Add an input to the decision.
     */
    public function input(string $name, mixed $value): self
    {
        $this->inputs[$name] = $value;
        return $this;
    }

    /**
     * Add multiple inputs at once.
     */
    public function inputs(array $inputs): self
    {
        $this->inputs = array_merge($this->inputs, $inputs);
        return $this;
    }

    /**
     * Create AND gate.
     */
    public function and(string $name, array $operands): self
    {
        $this->gates[$name] = [
            'operator' => 'and',
            'operands' => $operands,
        ];
        return $this;
    }

    /**
     * Create OR gate.
     */
    public function or(string $name, array $operands): self
    {
        $this->gates[$name] = [
            'operator' => 'or',
            'operands' => $operands,
        ];
        return $this;
    }

    /**
     * Create NOT gate.
     */
    public function not(string $name, string $operand): self
    {
        $this->gates[$name] = [
            'operator' => 'not',
            'operands' => [$operand],
        ];
        return $this;
    }

    /**
     * Create weighted gate.
     */
    public function weighted(string $name, array $operands, array $weights): self
    {
        $this->gates[$name] = [
            'operator' => 'weighted',
            'operands' => $operands,
            'weights' => $weights,
        ];
        return $this;
    }

    /**
     * Create consensus gate.
     */
    public function consensus(string $name, array $operands, array $options = []): self
    {
        $this->gates[$name] = [
            'operator' => 'consensus',
            'operands' => $operands,
        ] + $options;
        return $this;
    }

    /**
     * Set output gate.
     */
    public function output(string $gateName): self
    {
        $this->output = $gateName;
        return $this;
    }

    /**
     * Set decision name.
     */
    public function named(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Add context data.
     */
    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }

    /**
     * Evaluate the decision tree.
     */
    public function evaluate(): TernaryDecisionReport
    {
        $blueprint = [
            'name' => $this->name ?? 'fluent_decision',
            'inputs' => $this->inputs,
            'gates' => $this->gates,
        ];

        if ($this->output !== null) {
            $blueprint['output'] = $this->output;
        }

        return $this->engine->evaluate($blueprint, $this->context);
    }

    /**
     * Quick method: Require all inputs to be true.
     */
    public function requireAll(): self
    {
        $inputNames = array_keys($this->inputs);

        $this->gates['all_required'] = [
            'operator' => 'and',
            'operands' => $inputNames,
        ];

        $this->output = 'all_required';

        return $this;
    }

    /**
     * Quick method: Require any input to be true.
     */
    public function requireAny(): self
    {
        $inputNames = array_keys($this->inputs);

        $this->gates['any_required'] = [
            'operator' => 'or',
            'operands' => $inputNames,
        ];

        $this->output = 'any_required';

        return $this;
    }

    /**
     * Quick method: Get result as boolean.
     */
    public function toBool(bool $unknownAs = false): bool
    {
        return $this->evaluate()->result()->toBool($unknownAs);
    }

    /**
     * Quick method: Get result as TernaryState.
     */
    public function toState(): TernaryState
    {
        return $this->evaluate()->result();
    }

    /**
     * Get the raw blueprint (for debugging).
     */
    public function toBlueprint(): array
    {
        return [
            'name' => $this->name ?? 'fluent_decision',
            'inputs' => $this->inputs,
            'gates' => $this->gates,
            'output' => $this->output,
        ];
    }
}
