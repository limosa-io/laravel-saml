<?php

namespace ArieTimmerman\Laravel\SAML\Middleware;

use Closure;
use Illuminate\Http\Request;
use ArieTimmerman\Laravel\SAML\Helper;

class SAMLState
{
    public function handle(Request $request, Closure $next)
    {
        return tap($next($request), function () use ($request) {
            Helper::saveSAMLState($request);
        });
    }
}
