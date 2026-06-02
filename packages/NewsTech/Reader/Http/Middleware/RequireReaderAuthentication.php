<?php

namespace NewsTech\Reader\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireReaderAuthentication
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (! auth(config('newstech-reader.auth.guard'))->check()) {
            return redirect()->guest(route(config('newstech-reader.auth.login_route')));
        }

        return $next($request);
    }
}
