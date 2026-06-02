<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$group['title']"
    meta-description="Settings detail."
>
    <div class="space-y-6">
        @if (session('page_status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('page_status') }}
            </div>
        @endif

        <x-newstech::panel class="space-y-5 border-stone-200 bg-white p-8 text-stone-700 shadow-stone-200/60">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">{{ $group['title'] }}</h2>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.settings.index')"
                    tone="ghost"
                >
                    All Settings
                </x-newstech-admin::form.button>
            </div>

            @if ($groupSummaryText)
                <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                    {{ $groupSummaryText }}
                </div>
            @endif
        </x-newstech::panel>

        {!! newstech_view_render_event('admin.settings.group.before', ['group' => $group, 'settingsValues' => $settingsValues]) !!}
        {!! newstech_view_render_event('admin.settings.'.$group['key'].'.before', ['group' => $group, 'settingsValues' => $settingsValues]) !!}

        <form method="POST" action="{{ route('admin.newstech.settings.update', ['group' => $group['key']]) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {!! newstech_view_render_event('admin.settings.group.form.before', ['group' => $group, 'settingsValues' => $settingsValues]) !!}
            {!! newstech_view_render_event('admin.settings.'.$group['key'].'.form.before', ['group' => $group, 'settingsValues' => $settingsValues]) !!}

            @forelse ($group['sections'] as $section)
                <x-newstech-admin::form.section
                    :title="$section['name']"
                    :description="$section['info']"
                >
                    @foreach ($section['fields'] as $field)
                        @include('newstech-admin::settings._field', ['field' => $field, 'settingsValues' => $settingsValues])
                    @endforeach
                </x-newstech-admin::form.section>
            @empty
                <x-newstech::panel class="border-stone-200 bg-white p-6 text-sm leading-7 text-stone-600 shadow-stone-200/60">
                    <h3 class="text-lg font-bold tracking-tight text-stone-950">{{ $group['empty_state_title'] }}</h3>
                    <p class="mt-2">{{ $group['empty_state_description'] }}</p>
                </x-newstech::panel>
            @endforelse

            {!! newstech_view_render_event('admin.settings.group.form.after', ['group' => $group, 'settingsValues' => $settingsValues]) !!}
            {!! newstech_view_render_event('admin.settings.'.$group['key'].'.form.after', ['group' => $group, 'settingsValues' => $settingsValues]) !!}

            @if ($group['sections'] !== [])
                <div class="flex flex-wrap items-center justify-end gap-3">
                    <x-newstech-admin::form.button
                        :href="route('admin.newstech.settings.index')"
                        tone="ghost"
                    >
                        Back to settings
                    </x-newstech-admin::form.button>

                    <x-newstech-admin::form.button type="submit" tone="primary">
                        Save settings
                    </x-newstech-admin::form.button>
                </div>
            @endif
        </form>

        {!! newstech_view_render_event('admin.settings.group.after', ['group' => $group, 'settingsValues' => $settingsValues]) !!}
        {!! newstech_view_render_event('admin.settings.'.$group['key'].'.after', ['group' => $group, 'settingsValues' => $settingsValues]) !!}
    </div>
</x-newstech-admin::layouts.app>
