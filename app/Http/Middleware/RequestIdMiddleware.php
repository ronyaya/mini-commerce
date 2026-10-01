<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();

        Log::withContext([
            'request_id' => $requestId,
        ]);

        $startedAt = microtime(true);

        $response = $next($request);

        $durationMs = (microtime(true) - $startedAt) * 1000;

        Log::info('Request completed', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($durationMs, 2),
        ]);

        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
