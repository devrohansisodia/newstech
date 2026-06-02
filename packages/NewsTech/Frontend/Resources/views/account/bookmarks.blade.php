@php
    $seo = \NewsTech\Core\Support\SeoData::make(
        config('newstech.brand.name').' | Saved Articles',
        'Review and manage the published articles saved to your NewsTech reader account.',
        route('newstech.account.bookmarks')
    );
@endphp

<x-newstech-frontend::page-shell
    :seo="$seo"
    eyebrow="Reader Account"
    title="Saved articles"
    lead="These bookmarks persist with your reader account so you can return to important stories later."
>
    <div class="space-y-6">
        @include('newstech-frontend::account.partials.nav')

        @if (session('bookmark_status'))
            <x-newstech::panel class="border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                {{ session('bookmark_status') }}
            </x-newstech::panel>
        @endif

        <x-newstech::panel class="space-y-5 border-stone-200 bg-white p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-2xl font-black tracking-tight text-stone-950">Folders</h2>
                    <p class="text-sm leading-7 text-stone-600">Create simple folders to group saved articles and filter the bookmark list.</p>
                </div>

                <form method="POST" action="{{ route('newstech.account.bookmark-folders.store') }}" class="flex w-full max-w-xl flex-col gap-3 sm:flex-row">
                    @csrf
                    <input name="name" type="text" placeholder="Create a new folder" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                    <button type="submit" class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                        Add Folder
                    </button>
                </form>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('newstech.account.bookmarks') }}" class="rounded-full border {{ $activeFolder ? 'border-stone-200 bg-white text-stone-700' : 'border-amber-300 bg-amber-50 text-amber-700' }} px-4 py-2 text-sm font-semibold transition hover:border-amber-300 hover:bg-amber-50">
                    All Saved
                </a>

                @foreach ($folders as $folder)
                    <a href="{{ route('newstech.account.bookmarks', ['folder' => $folder->slug]) }}" class="rounded-full border {{ $activeFolder?->is($folder) ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-stone-200 bg-white text-stone-700' }} px-4 py-2 text-sm font-semibold transition hover:border-amber-300 hover:bg-amber-50">
                        {{ $folder->name }} ({{ $folder->bookmarks_count }})
                    </a>
                @endforeach
            </div>
        </x-newstech::panel>

        @if ($bookmarks && $bookmarks->count() > 0)
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($bookmarks as $bookmark)
                    <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-6">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">
                                <span>{{ $bookmark->article?->category?->name ?? 'General' }}</span>
                                <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                                <span>{{ $bookmark->article?->published_at?->format('M d, Y') ?? 'Published' }}</span>
                            </div>

                            <h2 class="text-2xl font-black tracking-tight text-stone-950">
                                <a href="{{ route('newstech.articles.show', ['slug' => $bookmark->article->slug]) }}" class="transition hover:text-amber-700">
                                    {{ $bookmark->article->title }}
                                </a>
                            </h2>

                            @if ($bookmark->article?->excerpt)
                                <p class="nt-line-clamp-4 text-sm leading-7 text-stone-600">{{ $bookmark->article->excerpt }}</p>
                            @endif

                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">
                                Folder: {{ $bookmark->folder?->name ?? 'Unsorted' }}
                            </p>
                        </div>

                        <div class="space-y-3">
                            <form method="POST" action="{{ route('newstech.bookmarks.folder.update', $bookmark) }}" class="flex flex-col gap-3 sm:flex-row">
                                @csrf
                                @method('PUT')
                                <select name="folder_id" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                                    <option value="">Unsorted</option>
                                    @foreach ($folders as $folder)
                                        <option value="{{ $folder->id }}" @selected($bookmark->folder?->is($folder))>{{ $folder->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center justify-center rounded-full border border-stone-200 bg-stone-50 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-amber-300 hover:bg-amber-50">
                                    Move
                                </button>
                            </form>

                            <div class="flex flex-wrap gap-3">
                            <a href="{{ route('newstech.articles.show', ['slug' => $bookmark->article->slug]) }}" class="inline-flex items-center rounded-full border border-stone-200 bg-stone-50 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-amber-300 hover:bg-amber-50">
                                Read Article
                            </a>

                            <form method="POST" action="{{ route('newstech.articles.bookmarks.destroy', ['slug' => $bookmark->article->slug]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                                    Remove
                                </button>
                            </form>
                            </div>
                        </div>
                    </x-newstech::panel>
                @endforeach
            </div>

            <div>
                {{ $bookmarks->links() }}
            </div>
        @else
            <x-newstech::panel class="border-dashed border-stone-300 bg-stone-50 p-6 text-sm leading-7 text-stone-500">
                No saved articles yet. Use the save action on published article pages to build your reading list.
            </x-newstech::panel>
        @endif
    </div>
</x-newstech-frontend::page-shell>
