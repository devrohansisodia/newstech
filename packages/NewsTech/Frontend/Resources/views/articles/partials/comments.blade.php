@php
    $honeypotField = config('newstech-comment.honeypot_field', 'company');
    $commentsEnabled = config('newstech-comment.enabled', true);
    $guestCommentsEnabled = config('newstech-comment.guest_comments_enabled', true);
    $websiteFieldEnabled = config('newstech-comment.website_field_enabled', true);
    $requiresModeration = config('newstech-comment.require_moderation', true);
    $reader = auth(config('newstech-reader.auth.guard'))->user();
@endphp

<section class="space-y-6">
    <x-newstech-frontend::section-heading
        eyebrow="Discussion"
        :title="$approvedCommentCount > 0 ? $approvedCommentCount.' Approved Comments' : 'Comments'"
        :description="$reader ? 'Signed-in readers can comment with their account identity. Moderation rules still apply.' : 'Guest comments are moderated before they appear publicly.'"
    />

    @if (session('comment_status'))
        <x-newstech::panel class="border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            {{ session('comment_status') }}
        </x-newstech::panel>
    @endif

    <div class="space-y-4">
        @forelse ($approvedComments as $comment)
            <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-5 text-sm leading-7 text-stone-700">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">
                        <span class="text-stone-900">{{ $comment->name }}</span>
                        @if ($comment->reader)
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[10px] tracking-[0.2em] text-amber-700">Reader</span>
                        @endif
                        <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                        <time datetime="{{ $comment->approved_at?->toIso8601String() ?? $comment->created_at?->toIso8601String() }}">
                            {{ ($comment->approved_at ?? $comment->created_at)?->format('M d, Y · H:i') }}
                        </time>
                        @if ($comment->website)
                            <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                            <a href="{{ $comment->website }}" class="text-amber-700 underline underline-offset-4" target="_blank" rel="noopener noreferrer nofollow">
                                Website
                            </a>
                        @endif
                    </div>

                    <p>{{ $comment->content }}</p>
                </div>

                @if ($commentsEnabled && ($guestCommentsEnabled || $reader))
                    <details class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <summary class="cursor-pointer text-sm font-semibold uppercase tracking-[0.2em] text-stone-600">
                            Reply
                        </summary>

                        <form method="POST" action="{{ route('newstech.articles.comments.store', ['slug' => $article->slug]) }}" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">

                            <div class="hidden" aria-hidden="true">
                                <label for="reply-{{ $comment->id }}-{{ $honeypotField }}">Do not fill this field</label>
                                <input id="reply-{{ $comment->id }}-{{ $honeypotField }}" name="{{ $honeypotField }}" type="text" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <input name="name" type="text" value="{{ old('name', $reader?->name) }}" @readonly($reader !== null) placeholder="Name" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-400 focus:outline-none">
                                <input name="email" type="email" value="{{ old('email', $reader?->email) }}" @readonly($reader !== null) placeholder="Email" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-400 focus:outline-none">
                            </div>

                            @if ($websiteFieldEnabled)
                                <input name="website" type="url" value="{{ old('website') }}" placeholder="Website (optional)" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-400 focus:outline-none">
                            @endif

                            <textarea name="content" rows="4" placeholder="Write your reply" class="w-full rounded-[1.5rem] border border-stone-200 bg-white px-4 py-3 text-sm leading-7 text-stone-700 focus:border-amber-400 focus:outline-none">{{ old('content') }}</textarea>

                            <button type="submit" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                                Submit Reply
                            </button>
                        </form>
                    </details>
                @endif

                @if ($comment->children->isNotEmpty())
                    <div class="space-y-4 border-l border-stone-200 pl-4 sm:pl-6">
                        @foreach ($comment->children as $reply)
                            <div class="space-y-3 rounded-2xl border border-stone-200 bg-stone-50 p-4 text-sm leading-7 text-stone-700">
                                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">
                                    <span class="text-stone-900">{{ $reply->name }}</span>
                                    @if ($reply->reader)
                                        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[10px] tracking-[0.2em] text-amber-700">Reader</span>
                                    @endif
                                    <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                                    <time datetime="{{ $reply->approved_at?->toIso8601String() ?? $reply->created_at?->toIso8601String() }}">
                                        {{ ($reply->approved_at ?? $reply->created_at)?->format('M d, Y · H:i') }}
                                    </time>
                                </div>

                                <p>{{ $reply->content }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-newstech::panel>
        @empty
            <x-newstech::panel class="border-dashed border-stone-300 bg-stone-50 p-5 text-sm leading-7 text-stone-500">
                No approved comments yet. Be the first to join the discussion.
            </x-newstech::panel>
        @endforelse
    </div>

    <x-newstech::panel class="border-stone-200 bg-white p-6 sm:p-8">
        <div class="space-y-5">
            <div>
                <h3 class="text-2xl font-black tracking-tight text-stone-950">Leave a comment</h3>
                <p class="mt-2 text-sm leading-7 text-stone-600">
                    {{ $requiresModeration ? 'Your comment will be reviewed before it appears on the article.' : 'Clean comments can appear immediately after submission.' }}
                </p>
            </div>

            @if (! $commentsEnabled)
                <x-newstech::panel class="border-dashed border-stone-300 bg-stone-50 p-5 text-sm leading-7 text-stone-500">
                    Comments are closed.
                </x-newstech::panel>
            @elseif (! $guestCommentsEnabled && ! $reader)
                <x-newstech::panel class="border-dashed border-stone-300 bg-stone-50 p-5 text-sm leading-7 text-stone-500">
                    Guest comments are currently disabled.
                </x-newstech::panel>
            @else
                <form method="POST" action="{{ route('newstech.articles.comments.store', ['slug' => $article->slug]) }}" class="space-y-5">
                    @csrf

                    <div class="hidden" aria-hidden="true">
                        <label for="comment-{{ $honeypotField }}">Do not fill this field</label>
                        <input id="comment-{{ $honeypotField }}" name="{{ $honeypotField }}" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="comment-name" class="text-sm font-semibold text-stone-900">Name</label>
                            <input id="comment-name" name="name" type="text" value="{{ old('name', $reader?->name) }}" @readonly($reader !== null) class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-400 focus:outline-none">
                            @if ($reader)
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">Submitted with your reader account name.</p>
                            @endif
                            @error('name')
                                <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="comment-email" class="text-sm font-semibold text-stone-900">Email</label>
                            <input id="comment-email" name="email" type="email" value="{{ old('email', $reader?->email) }}" @readonly($reader !== null) class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-400 focus:outline-none">
                            @if ($reader)
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">Submitted with your reader account email.</p>
                            @endif
                            @error('email')
                                <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if ($websiteFieldEnabled)
                        <div class="space-y-2">
                            <label for="comment-website" class="text-sm font-semibold text-stone-900">Website <span class="text-stone-400">(optional)</span></label>
                            <input id="comment-website" name="website" type="url" value="{{ old('website') }}" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-400 focus:outline-none">
                            @error('website')
                                <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="space-y-2">
                        <label for="comment-content" class="text-sm font-semibold text-stone-900">Comment</label>
                        <textarea id="comment-content" name="content" rows="6" class="w-full rounded-[1.5rem] border border-stone-200 bg-white px-4 py-3 text-sm leading-7 text-stone-700 focus:border-amber-400 focus:outline-none">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                        Submit Comment
                    </button>
                </form>
            @endif
        </div>
    </x-newstech::panel>
</section>
