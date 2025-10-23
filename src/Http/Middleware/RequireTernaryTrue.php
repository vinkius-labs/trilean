<?php

namespace VinkiusLabs\Trilean\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use VinkiusLabs\Trilean\Enums\TernaryState;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simpler middleware that requires a single ternary attribute to be TRUE.
 * 
 * Usage:
 * Route::post('/publish')->middleware('ternary.require:can_publish');
 */
class RequireTernaryTrue
{
    public function handle(Request $request, Closure $next, string $attribute): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Authentication required');
        }

        $value = $user->$attribute ?? $request->input($attribute);
        $state = TernaryState::fromMixed($value);

        if (!$state->isTrue()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Attribute '{$attribute}' must be TRUE",
                    'current_state' => $state->value,
                    'attribute' => $attribute,
                ], 403);
            }

            abort(403, "Access denied: {$attribute} requirement not met");
        }

        return $next($request);
    }
}
