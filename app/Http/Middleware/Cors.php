<?php

namespace App\Http\Middleware;
use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Content-Range, Content-Disposition, Content-Description, X-Auth-Token');
        $response->header('Access-Control-Allow-Origin', '*');

        $response->header('Access-Control-Allow-Methods', '*');
        $response->header('Access-Control-Allow-Headers', '*');
        $response->header('Content-Type: application/json', '*');
        return $response;
    }
}
