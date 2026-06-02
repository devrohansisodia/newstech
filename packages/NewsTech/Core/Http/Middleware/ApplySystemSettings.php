<?php

namespace NewsTech\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use NewsTech\Core\Support\SystemSettingsManager;
use Symfony\Component\HttpFoundation\Response;

class ApplySystemSettings
{
    public function __construct(protected SystemSettingsManager $systemSettingsManager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->systemSettingsManager->bootConfig();

        return $next($request);
    }
}
