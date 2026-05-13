<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            Log::warning('Unauthorized admin access attempt', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'user_id' => $request->user()?->id,
            ]);

            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
