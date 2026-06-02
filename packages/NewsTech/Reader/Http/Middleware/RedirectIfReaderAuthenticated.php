<?php

namespace NewsTech\Reader\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfReaderAuthenticated
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (auth(config('newstech-reader.auth.guard'))->check()) {
            return redirect()->route(config('newstech-reader.auth.redirect_to'));
        }

        return $next($request);
    }
}
