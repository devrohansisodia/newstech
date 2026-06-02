<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Comments'"
    meta-description="Comment moderation."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <h2 class="text-3xl font-black tracking-tight text-stone-950">Comments</h2>
        </x-newstech::panel>

        @if (session('comment_status'))
            <x-newstech::panel class="border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800">
                {{ session('comment_status') }}
            </x-newstech::panel>
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            <x-newstech-admin::stat-card
                eyebrow="Volume"
                title="Total Comments"
                :value="$commentCount"
                description="All guest and reader comments currently stored for editorial moderation."
            />

            <x-newstech-admin::stat-card
                eyebrow="Queue"
                title="Pending Comments"
                :value="$pendingCommentCount"
                description="Submitted comments that are still awaiting moderator review."
            />

            <x-newstech-admin::stat-card
                eyebrow="Public"
                title="Approved Comments"
                :value="$approvedCommentCount"
                description="Comments that are currently visible on published article pages."
            />
        </div>

        <div class="flex flex-wrap gap-3">
            @php
                $filters = [
                    'all' => 'All ('.$commentCount.')',
                    'pending' => 'Pending ('.$pendingCommentCount.')',
                    'approved' => 'Approved ('.$approvedCommentCount.')',
                    'rejected' => 'Rejected ('.$rejectedCommentCount.')',
                    'spam' => 'Spam ('.$spamCommentCount.')',
                ];
            @endphp

            @foreach ($filters as $filterKey => $filterLabel)
                <x-newstech-admin::form.button
                    :href="route('admin.newstech.comments.index', $filterKey === 'all' ? [] : ['filter' => $filterKey])"
                    :tone="$activeFilter === $filterKey ? 'primary' : 'neutral'"
                >
                    {{ $filterLabel }}
                </x-newstech-admin::form.button>
            @endforeach
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
