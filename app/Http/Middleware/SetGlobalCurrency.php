<?php

namespace App\Http\Middleware;

use App\Models\CurrencySetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetGlobalCurrency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get currency from session or use default
        $currency = session('currency', CurrencySetting::getDefaultCurrency());

        // Share with all views
        view()->share('currency', $currency);

        // For use in controllers/services
        app()->instance('currency', $currency);

        return $next($request);
    }
}
