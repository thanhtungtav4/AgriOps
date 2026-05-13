<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FarmScopeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => [
                    'code' => 'AUTH_TOKEN_MISSING',
                    'message' => 'Authentication required.',
                    'trace_id' => $request->header('X-Trace-ID') ?? uniqid(),
                ],
            ], 401);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->farm_id) {
            return response()->json([
                'error' => [
                    'code' => 'AUTH_FORBIDDEN',
                    'message' => 'User is not assigned to any farm.',
                    'details' => [
                        'required_role' => 'admin or farm-scoped role',
                        'current_role' => $user->role,
                    ],
                    'trace_id' => $request->header('X-Trace-ID') ?? uniqid(),
                ],
            ], 403);
        }

        $request->attributes->set('farm_scope', $user->farm_id);

        return $next($request);
    }
}
