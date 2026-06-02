<?php

namespace NewsTech\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (auth(config('newstech-admin.auth.guard'))->check()) {
            return redirect()->route(config('newstech-admin.auth.redirect_to'));
        }

        return $next($request);
    }
}
