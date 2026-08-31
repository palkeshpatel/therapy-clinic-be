<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $origin  = $request->headers->get('Origin', '');
        $allowed = array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', ''))));

        if (empty($allowed) || in_array('*', $allowed, true)) {
            $allowOrigin = $origin ?: '*';
        } else {
            $allowOrigin = in_array($origin, $allowed, true) ? $origin : ($allowed[0] ?? '*');
        }

        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)->render($request, $e);
        }

        if (method_exists($response, 'header')) {
            $response->header('Access-Control-Allow-Origin', $allowOrigin);
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
            $response->header('Access-Control-Allow-Credentials', 'true');
        } elseif (property_exists($response, 'headers')) {
            $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
