<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Review Comment'"
    meta-description="NewsTech comment moderation detail."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-600">Moderation Detail</p>
            <h2 class="text-3xl font-black tracking-tight text-stone-950">Review Comment</h2>
            <p class="max-w-3xl text-base leading-8 text-stone-600">
                Review the full comment, confirm the related article, and approve, reject, or delete it.
            </p>
        </x-newstech::panel>

        @if (session('comment_status'))
            <x-newstech::panel class="border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800">
                {{ session('comment_status') }}
            </x-newstech::panel>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.8fr)]">
            <x-newstech-admin::form.section
                title="Comment Content"
                description="Comments remain moderation-first. Only approved comments are rendered on the public article detail page."
            >
                <div class="space-y-4 text-sm leading-7 text-stone-700">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-400">Commenter</p>
                        <p class="mt-2 font-semibold text-stone-950">{{ $comment->name }}</p>
                        <p class="text-stone-600">{{ $comment->email }}</p>
                        @if ($comment->reader)
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-600">
                                Reader account linked · {{ $comment->reader->name }} · {{ $comment->reader->email }}
                            </p>
                        @endif
                        @if ($comment->website)
                            <a href="{{ $comment->website }}" class="text-amber-700 underline underline-offset-4" target="_blank" rel="noopener noreferrer">
                                {{ $comment->website }}
                            </a>
                        @endif
                        @if ($comment->is_spam)
                            <p class="mt-2 inline-flex rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-rose-700">
                                Spam Flagged
                            </p>
                        @endif
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-400">Comment</p>
                        <div class="mt-2 rounded-2xl border border-stone-200 bg-white p-5">
                            {{ $comment->content }}
                        </div>
                    </div>
                </div>
            </x-newstech-admin::form.section>

            <div class="space-y-6">
                <x-newstech-admin::form.section
                    title="Comment Status"
                    description="Moderation state determines whether this comment is visible on the related article."
                >
                    <div class="space-y-3 text-sm text-stone-600">
                        <p><span class="font-semibold text-stone-950">Article:</span> {{ $comment->article?->title ?? 'Unknown article' }}</p>
                        @if ($comment->article)
                            <a href="{{ route('newstech.articles.show', ['slug' => $comment->article->slug]) }}" class="text-amber-700 underline underline-offset-4" target="_blank" rel="noopener noreferrer">
                                View public article
                            </a>
                        @endif
                        <p><span class="font-semibold text-stone-950">Submitted:</span> {{ $comment->created_at?->format('M d, Y H:i') }}</p>
                        <p><span class="font-semibold text-stone-950">Status:</span> {{ $comment->getStatusLabel() }}</p>
                        <p><span class="font-semibold text-stone-950">Comment Source:</span> {{ $comment->reader ? 'Reader account' : 'Guest submission' }}</p>
                        <p><span class="font-semibold text-stone-950">Thread:</span> {{ $comment->parent ? 'Reply to #'.$comment->parent->getKey() : 'Top-level comment' }}</p>
                        <p><span class="font-semibold text-stone-950">IP Address:</span> {{ $comment->ip_address ?: 'Not recorded' }}</p>
                        <p><span class="font-semibold text-stone-950">User Agent:</span> {{ $comment->user_agent ?: 'Not recorded' }}</p>
                        <p><span class="font-semibold text-stone-950">Spam Flag:</span> {{ $comment->is_spam ? 'Yes' : 'No' }}</p>
                        @if ($comment->spam_reason)
                            <p><span class="font-semibold text-stone-950">Spam Reason:</span> {{ str($comment->spam_reason)->replace('_', ' ')->title() }}</p>
                        @endif
                        <p><span class="font-semibold text-stone-950">Moderated:</span> {{ $comment->moderated_at?->format('M d, Y H:i') ?? 'Not moderated yet' }}</p>
                        @if ($comment->moderator)
                            <p><span class="font-semibold text-stone-950">Moderator:</span> {{ $comment->moderator->name }} · {{ $comment->moderator->email }}</p>
                        @endif
                    </div>
                </x-newstech-admin::form.section>

                <x-newstech-admin::form.section
                    title="Moderation Actions"
                    description="Use simple moderation controls for the comments foundation."
                >
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('admin.newstech.comments.approve', $comment) }}">
                            @csrf
                            @method('PUT')
                            <x-newstech-admin::form.button type="submit" tone="primary">Approve</x-newstech-admin::form.button>
                        </form>

                        <form method="POST" action="{{ route('admin.newstech.comments.reject', $comment) }}">
                            @csrf
                            @method('PUT')
                            <x-newstech-admin::form.button type="submit" tone="ghost">Reject</x-newstech-admin::form.button>
                        </form>

                        <form method="POST" action="{{ route('admin.newstech.comments.destroy', $comment) }}">
                            @csrf
                            @method('DELETE')
                            <x-newstech-admin::form.button type="submit" tone="neutral">Delete</x-newstech-admin::form.button>
                        </form>
                    </div>
                </x-newstech-admin::form.section>
            </div>
        </div>
    </div>
</x-newstech-admin::layouts.app>
