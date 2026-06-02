<?php

namespace NewsTech\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (! auth(config('newstech-admin.auth.guard'))->check()) {
            return redirect()->guest(route(config('newstech-admin.auth.login_route')));
        }

        return $next($request);
    }
}
