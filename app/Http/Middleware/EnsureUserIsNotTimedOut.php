<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotTimedOut
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'isTimedOut') || ! $user->isTimedOut()) {
            return $next($request);
        }

        if ($user->tipo_usuario === 'root') {
            return $next($request);
        }

        if ($request->routeIs('home') || $request->routeIs('logout')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sua conta esta em timeout temporario.',
                'timeout_until' => optional($user->timeout_until)->toDateTimeString(),
            ], 423);
        }

        return redirect()
            ->route('home')
            ->with('timeout_notice', 'Sua conta esta em timeout temporario: ' . $user->timeoutLabel() . '.');
    }
}
