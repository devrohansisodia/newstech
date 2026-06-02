<?php

namespace NewsTech\Seo\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use NewsTech\Core\Support\SystemSettingsManager;
use NewsTech\Seo\Http\Requests\Admin\AnalyzeSeoRequest;
use NewsTech\Seo\Support\SeoAnalyzer;

class SeoAnalysisController
{
    public function __invoke(
        AnalyzeSeoRequest $request,
        SeoAnalyzer $seoAnalyzer,
        SystemSettingsManager $systemSettingsManager,
    ): JsonResponse {
        $systemSettingsManager->bootConfig();

        return response()->json(
            $seoAnalyzer->analyze($request->validated())->toArray()
        );
    }
}
