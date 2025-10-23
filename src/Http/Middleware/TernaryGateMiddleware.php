<?php

namespace VinkiusLabs\Trilean\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use VinkiusLabs\Trilean\Enums\TernaryState;
use Symfony\Component\HttpFoundation\Response;

/**
 * Intelligent middleware that makes ternary decisions based on request context.
 * 
 * Usage in routes:
 * Route::get('/api/data')->middleware('ternary.gate:verified,active,consented');
 * Route::get('/admin')->middleware('ternary.require:is_admin');
 */
class TernaryGateMiddleware
{
    /**
     * Handle an incoming request with ternary logic.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $keys  Comma-separated keys to check
     * @param  string  $operator  Logic operator (and, or, consensus)
     * @param  string  $expected  Expected state (true, false, unknown)
     */
    public function handle(Request $request, Closure $next, string $keys, string $operator = 'and', string $expected = 'true'): Response
    {
        $keyList = explode(',', $keys);
        $user = $request->user();

        if (!$user) {
            return $this->handleUnauthorized($request, 'User not authenticated');
        }

        $values = collect($keyList)->map(function ($key) use ($user, $request) {
            $key = trim($key);

            // Try user attribute first
            if (isset($user->$key)) {
                return $user->$key;
            }

            // Try request input
            if ($request->has($key)) {
                return $request->input($key);
            }

            // Try method call
            if (method_exists($user, $key)) {
                return $user->$key();
            }

            return null;
        });

        $state = match ($operator) {
            'and' => trilean()->and(...$values),
            'or' => trilean()->or(...$values),
            'consensus' => trilean()->consensus($values),
            'weighted' => trilean()->weighted($values, $this->parseWeights($request)),
            default => TernaryState::UNKNOWN,
        };

        $expectedState = TernaryState::fromMixed($expected);

        if ($state !== $expectedState) {
            return $this->handleForbidden($request, $state, $expectedState, $keyList);
        }

        // Add ternary context to request for downstream use
        $request->merge([
            '_ternary_gate' => [
                'keys' => $keyList,
                'state' => $state->value,
                'operator' => $operator,
            ]
        ]);

        return $next($request);
    }

    protected function handleUnauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'state' => 'unknown',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    protected function handleForbidden(Request $request, TernaryState $actual, TernaryState $expected, array $keys): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Access denied',
                'ternary_state' => $actual->value,
                'expected_state' => $expected->value,
                'checked_keys' => $keys,
            ], 403);
        }

        abort(403, 'Access denied based on ternary evaluation');
    }

    protected function parseWeights(Request $request): array
    {
        if ($request->has('_weights')) {
            return array_map('intval', explode(',', $request->input('_weights')));
        }

        return [];
    }
}
