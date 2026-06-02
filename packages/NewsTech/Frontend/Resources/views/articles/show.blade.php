@php
    $featuredImage = $article->featured_image_url;
    $articleUrl = route('newstech.articles.show', ['slug' => $article->slug]);
    $shareText = rawurlencode($article->title);
    $shareUrl = rawurlencode($articleUrl);
@endphp

<x-newstech-frontend::layouts.app
    :title="$seo->title"
    :meta-description="$seo->description"
    :seo="$seo"
>
    <div class="space-y-12">
        {!! newstech_view_render_event('frontend.article.show.top.before', ['article' => $article]) !!}
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">
            <a href="{{ route('newstech.home') }}" class="transition hover:text-stone-950">Home</a>

            @if ($article->category)
                <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                <a href="{{ route('newstech.categories.show', ['slug' => $article->category->slug]) }}" class="transition hover:text-stone-950">{{ $article->category->name }}</a>
            @endif

            <span class="h-1 w-1 rounded-full bg-stone-300"></span>
            <span class="text-stone-700">{{ $article->title }}</span>
        </nav>

        <section class="grid gap-8 xl:grid-cols-[minmax(0,1.45fr)_minmax(20rem,0.75fr)]">
            <article class="space-y-8">
                <header class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">
                        @if ($article->category)
                            <a href="{{ route('newstech.categories.show', ['slug' => $article->category->slug]) }}" class="rounded-full border border-stone-200 bg-white px-4 py-2 transition hover:border-amber-300 hover:bg-amber-50">{{ $article->category->name }}</a>
                        @endif

                        @if ($article->is_breaking)
                            <span class="rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-rose-700">Breaking</span>
                        @endif

                        @if ($article->is_featured)
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-amber-700">Featured</span>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <h1 class="max-w-5xl text-4xl font-black tracking-tight text-stone-950 sm:text-5xl lg:text-6xl">
                            {{ $article->title }}
                        </h1>

                        @if ($article->excerpt)
                            <p class="max-w-4xl text-lg leading-8 text-stone-600 sm:text-xl">
                                {{ $article->excerpt }}
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-3 text-sm text-stone-500">
                        <span>{{ $article->author?->name ?? 'News Desk' }}</span>
                        <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                        <time datetime="{{ optional($article->published_at)->toIso8601String() }}">
                            {{ $article->published_at?->format('M d, Y · H:i') ?? 'Published' }}
                        </time>

                        @if ($article->updated_at && $article->updated_at->ne($article->published_at))
                            <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                            <span>Updated {{ $article->updated_at->format('M d, Y · H:i') }}</span>
                        @endif
                    </div>

                    {!! newstech_view_render_event('frontend.article.show.meta.after', ['article' => $article]) !!}
                </header>

                @if ($featuredImage)
                    <div class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white">
                        <img
                            src="{{ $featuredImage }}"
                            alt="{{ $article->title }}"
                            class="h-full max-h-[34rem] w-full object-cover"
                        >
                    </div>
                @else
                    <div class="overflow-hidden rounded-[2rem] border border-stone-200 bg-gradient-to-br from-stone-900 via-stone-800 to-amber-700 px-8 py-16 text-white">
                        <div class="max-w-3xl space-y-4">
                            <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em]">NewsTech</span>
                            <h2 class="text-3xl font-black tracking-tight sm:text-4xl">Featured image not set yet</h2>
                            <p class="text-base leading-8 text-stone-100 sm:text-lg">
                                This story still renders cleanly without a remote fallback. Add a featured image in the admin editor for stronger homepage, article, and social presentation.
                            </p>
                        </div>
                    </div>
                @endif

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_16rem]">
                    <div class="space-y-8">
                        {!! newstech_view_render_event('frontend.article.show.content.before', ['article' => $article]) !!}
                        @if ($article->content)
                            <div class="rounded-[2rem] border border-stone-200 bg-white p-6 sm:p-8">
                                <div class="nt-prose sm:text-lg" data-rich-content>
                                    {!! app(\NewsTech\Core\Support\RichTextContentRenderer::class)->render($article->content) !!}
                                </div>
                            </div>
                        @endif

                        {!! newstech_view_render_event('frontend.article.show.content.after', ['article' => $article]) !!}

                        @if ($article->tags->isNotEmpty())
                            <section class="space-y-4">
                                <x-newstech-frontend::section-heading
                                    eyebrow="Tags"
                                    title="Related topics"
                                    description="Topic pages for article tags are now available from the frontend taxonomy layer."
                                />

                                <div class="flex flex-wrap gap-3">
                                    @foreach ($article->tags as $tag)
                                        <a href="{{ route('newstech.tags.show', ['slug' => $tag->slug]) }}" class="rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-amber-300 hover:bg-amber-50">
                                            {{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @include('newstech-frontend::articles.partials.comments', [
                            'article' => $article,
                            'approvedComments' => $approvedComments,
                            'approvedCommentCount' => $approvedCommentCount,
                        ])

                        {!! newstech_view_render_event('frontend.article.show.comments.after', ['article' => $article]) !!}
                    </div>

                    <aside class="space-y-4">
                        {!! newstech_view_render_event('frontend.article.show.reader_tools.before', ['article' => $article]) !!}
                        <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-5 text-stone-700">
                            <h2 class="text-lg font-bold tracking-tight text-stone-950">Save this story</h2>

                            @if (session('bookmark_status'))
                                <p class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                                    {{ session('bookmark_status') }}
                                </p>
                            @endif

                            @if (auth(config('newstech-reader.auth.guard'))->check())
                                @if ($isBookmarked)
                                    <form method="POST" action="{{ route('newstech.articles.bookmarks.destroy', ['slug' => $article->slug]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                                            Remove Bookmark
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('newstech.articles.bookmarks.store', ['slug' => $article->slug]) }}" class="space-y-3">
                                        @csrf
                                        @if ($bookmarkFolders->isNotEmpty())
                                            <select name="folder_id" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                                                <option value="">Save without folder</option>
                                                @foreach ($bookmarkFolders as $folder)
                                                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                        <button type="submit" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                                            Save Article
                                        </button>
                                    </form>
                                @endif
                            @else
                                <p class="text-sm leading-7 text-stone-600">
                                    <a href="{{ route('newstech.readers.login') }}" class="font-semibold text-amber-700 underline underline-offset-4">Login to save this article</a>
                                    and keep it in your personal reading list.
                                </p>
                            @endif
                        </x-newstech::panel>

                        <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-5 text-stone-700">
                            <h2 class="text-lg font-bold tracking-tight text-stone-950">Share this story</h2>
                            <div class="grid gap-3 text-sm font-semibold">
                                <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ $shareUrl }}" class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 transition hover:border-amber-300 hover:bg-amber-50">
                                    Share on X
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 transition hover:border-amber-300 hover:bg-amber-50">
                                    Share on Facebook
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 transition hover:border-amber-300 hover:bg-amber-50">
                                    Share on LinkedIn
                                </a>
                            </div>
                        </x-newstech::panel>

                        <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-5 text-sm leading-7 text-stone-600">
                            <h2 class="text-lg font-bold tracking-tight text-stone-950">Story details</h2>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-400">Section</dt>
                                    <dd class="text-stone-700">
                                        @if ($article->category)
                                            <a href="{{ route('newstech.categories.show', ['slug' => $article->category->slug]) }}" class="transition hover:text-stone-950">{{ $article->category->name }}</a>
                                        @else
                                            General
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-400">Author</dt>
                                    <dd class="text-stone-700">
                                        @if ($article->author)
                                            <a href="{{ route('newstech.authors.show', ['slug' => $article->author->slug]) }}" class="transition hover:text-stone-950">{{ $article->author->name }}</a>
                                        @else
                                            News Desk
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-400">Published</dt>
                                    <dd class="text-stone-700">{{ $article->published_at?->format('M d, Y H:i') ?? 'Published' }}</dd>
                                </div>
                            </dl>
                        </x-newstech::panel>
                        {!! newstech_view_render_event('frontend.article.show.reader_tools.after', ['article' => $article]) !!}
                    </aside>
                </div>
            </article>

            <aside class="space-y-8">
                {!! newstech_view_render_event('frontend.article.show.sidebar.top', ['article' => $article]) !!}

                <section class="space-y-4">
                    <x-newstech-frontend::section-heading
                        eyebrow="Related"
                        title="Related stories"
                        description="More published coverage from the same section or the latest newsroom feed."
                    />

                    <div class="space-y-4">
                        @forelse ($relatedArticles as $relatedArticle)
                            <x-newstech-frontend::article-list-item :article="$relatedArticle" />
                        @empty
                            <x-newstech::panel class="border-stone-200 bg-white p-5 text-sm leading-7 text-stone-600">
                                Related published stories will appear here as more content is added.
                            </x-newstech::panel>
                        @endforelse
                    </div>
                </section>

                <section class="space-y-4">
                    <x-newstech-frontend::section-heading
                        eyebrow="Latest"
                        title="Latest published articles"
                        description="Fresh reporting from the front page feed."
                    />

                    <div class="space-y-4">
                        @foreach ($latestArticles as $latestArticle)
                            <x-newstech-frontend::article-list-item :article="$latestArticle" />
                        @endforeach
                    </div>
                </section>

                <x-newstech-frontend::newsletter-form
                    source="article"
                    title="Follow the newsroom by email"
                    description="The article page now includes a reusable subscription form so readers can opt into future newsletter updates."
                    compact
                />
                {!! newstech_view_render_event('frontend.article.show.sidebar.bottom', ['article' => $article]) !!}
            </aside>
        </section>
        {!! newstech_view_render_event('frontend.article.show.bottom', ['article' => $article]) !!}
    </div>
</x-newstech-frontend::layouts.app>
