<?php

namespace NewsTech\Admin\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use NewsTech\Admin\Support\SettingsGroupManager;
use NewsTech\Core\Support\SystemSettingsManager;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsGroupManager $settingsGroupManager,
        protected SystemSettingsManager $systemSettingsManager,
    ) {}

    public function index(): View
    {
        $groups = collect($this->settingsGroupManager->all())
            ->map(function (array $group): array {
                $settingsValues = $this->settingsGroupManager->values($group, $this->systemSettingsManager);
                $group['settings_values'] = $settingsValues;
                $group['summary_text'] = $this->settingsGroupManager->summary($group, $settingsValues);

                return $group;
            })
            ->all();

        return view('newstech-admin::settings.index', [
            'groups' => $groups,
        ]);
    }

    public function show(string $group): View
    {
        $groupDefinition = $this->settingsGroupManager->findOrFail($group);
        $settingsValues = $this->settingsGroupManager->values($groupDefinition, $this->systemSettingsManager);

        return view('newstech-admin::settings.show', [
            'group' => $groupDefinition,
            'settingsValues' => $settingsValues,
            'groupSummaryText' => $this->settingsGroupManager->summary($groupDefinition, $settingsValues),
        ]);
    }

    public function update(Request $request, ?string $group = null): RedirectResponse
    {
        $groupKey = $group ?? (string) $request->input('settings_group');
        $groupDefinition = $this->settingsGroupManager->findOrFail($groupKey);
        $validated = $this->settingsGroupManager->validate($groupDefinition, $request->all());

        $this->settingsGroupManager->save($groupDefinition, $request, $validated);

        return redirect()
            ->route('admin.newstech.settings.show', ['group' => $groupDefinition['key']])
            ->with('page_status', $groupDefinition['title'].' updated successfully.');
    }
}
