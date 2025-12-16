<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Если маршруты устаревают/переименовываются, то сообщим об этом
 */
class DeprecationWarning
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Проверяем, является ли маршрут устаревшим
        if ($request->routeIs('company.old')) {
            $response->headers->set('Deprecation', 'true');
            $response->headers->set('Sunset', now()->addMonths(6)->toRfc7231String());
            $response->headers->set('Link', '</api/v1/companies>; rel="successor-version"');
        }

        return $response;
    }
}

