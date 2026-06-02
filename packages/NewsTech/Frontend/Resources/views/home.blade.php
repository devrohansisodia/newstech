<x-newstech-frontend::layouts.app
    :title="$seo->title"
    :meta-description="$seo->description"
    :seo="$seo"
>
    <div class="space-y-12" data-homepage-layout="{{ $homepageLayout }}">
        {!! newstech_view_render_event('frontend.homepage.top.before', ['homepageLayout' => $homepageLayout]) !!}
        <div @class([
            'space-y-12',
            'xl:grid xl:grid-cols-10 xl:gap-8 xl:space-y-0' => $homepageLayout === 'two_column_70_30',
        ])>
            <div
                @class([
                    'space-y-12',
                    'xl:col-span-7' => $homepageLayout === 'two_column_70_30',
                ])
                data-homepage-main="{{ $homepageLayout }}"
            >
                @if ($heroArticle)
                    <section class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(20rem,0.85fr)]">
                        <div class="space-y-6">
                            <x-newstech-frontend::section-heading
                                eyebrow="Top Story"
                                title="Latest main story"
                                description="The front page leads with the newest published story from the newsroom."
                            />

                            <x-newstech-frontend::article-card :article="$heroArticle" tone="hero" />
                        </div>

                        <div class="space-y-6">
                            <x-newstech-frontend::section-heading
                                eyebrow="Featured"
                                title="Editor picks"
                                description="Highlighted coverage pulled from articles marked as featured."
                            />

                            <div class="space-y-4">
                                @forelse ($featuredArticles as $article)
                                    <x-newstech-frontend::article-list-item :article="$article" />
                                @empty
                                    <x-newstech::panel class="border-stone-200 bg-white p-5 text-sm leading-7 text-stone-600">
                                        Featured stories will appear here when editors mark published articles as featured.
                                    </x-newstech::panel>
                                @endforelse
                            </div>
                        </div>
                    </section>
                @else
                    <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-8 text-stone-700">
                        <x-newstech-frontend::section-heading
                            eyebrow="Homepage"
                            title="No published stories yet"
                            description="Publish an article from the admin panel to populate the homepage hero, latest feed, featured stories, breaking strip, and category blocks."
                        />
                    </x-newstech::panel>
                @endif

                {!! newstech_view_render_event('frontend.homepage.top.after', ['homepageLayout' => $homepageLayout]) !!}

                <section class="space-y-5">
                    <x-newstech-frontend::section-heading
                        eyebrow="Breaking News"
                        title="Breaking news strip"
                        description="Only published articles marked as breaking appear in this strip."
                    />

                    <div class="overflow-hidden rounded-2xl border border-rose-200 bg-rose-50/80">
                        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center">
                            <span class="inline-flex items-center rounded-xl border border-rose-200 bg-white px-4 py-2 text-xs font-black uppercase tracking-[0.3em] text-rose-700">
                                Breaking
                            </span>

                            <div class="flex flex-1 flex-wrap gap-3">
                                @forelse ($breakingArticles as $article)
                                    <a
                                        href="{{ route('newstech.articles.show', ['slug' => $article->slug]) }}"
                                        class="rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-stone-800 transition hover:border-rose-300 hover:bg-rose-100/40"
                                    >
                                        {{ $article->title }}
                                    </a>
                                @empty
                                    <p class="text-sm text-stone-600">No breaking stories are currently flagged.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <x-newstech-frontend::section-heading
                        eyebrow="Latest"
                        title="Latest news"
                        description="The latest published stories from across the newsroom."
                    />

                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @forelse ($latestArticles as $article)
                            <x-newstech-frontend::article-card :article="$article" />
                        @empty
                            <x-newstech::panel class="border-stone-200 bg-white p-5 text-sm leading-7 text-stone-600 md:col-span-2 xl:col-span-3">
                                Latest published stories will appear here once the newsroom publishes more articles.
                            </x-newstech::panel>
                        @endforelse
                    </div>
                </section>

                <section class="space-y-6">
                    <x-newstech-frontend::section-heading
                        eyebrow="Featured"
                        title="Featured articles"
                        description="A reusable spotlight section for front-page editorial picks."
                    />

                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @forelse ($featuredArticles as $article)
                            <x-newstech-frontend::article-card :article="$article" />
                        @empty
                            <x-newstech::panel class="border-stone-200 bg-white p-5 text-sm leading-7 text-stone-600 md:col-span-2 xl:col-span-3">
                                Featured coverage will appear here when editors mark published articles as featured.
                            </x-newstech::panel>
                        @endforelse
                    </div>
                </section>

                <x-newstech-frontend::newsletter-form
                    source="homepage"
                    title="Start the NewsTech newsletter list"
                    description="Readers can subscribe from the homepage now, while campaign sending and editorial newsletters remain deferred to a later phase."
                />

                @if ($homepageLayout === 'full_width')
                    {!! newstech_view_render_event('frontend.homepage.sidebar.inline') !!}
                @endif

                @foreach ($categoryBlocks as $block)
                    <section class="space-y-6">
                        <x-newstech-frontend::section-heading
                            eyebrow="Category"
                            :title="$block['section_title']"
                            :description="'Published coverage from the '.$block['category']->name.' desk.'"
                        />

                        <div class="grid gap-5 lg:grid-cols-[minmax(0,1.25fr)_minmax(0,0.95fr)]">
                            <x-newstech-frontend::article-card :article="$block['articles']->first()" />

                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ($block['articles']->slice(1) as $article)
                                    <x-newstech-frontend::article-list-item :article="$article" />
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            @if ($homepageLayout === 'two_column_70_30')
                <aside class="space-y-5 xl:col-span-3 xl:sticky xl:top-6 xl:self-start" data-homepage-sidebar>
                    {!! newstech_view_render_event('frontend.homepage.sidebar.top') !!}
                    <x-newstech-frontend::homepage-sidebar
                        :sidebar="$homepageSidebar"
                        :featured-articles="$featuredArticles"
                        :latest-articles="$latestArticles"
                    />
                    {!! newstech_view_render_event('frontend.homepage.sidebar.bottom') !!}
                </aside>
            @endif
        </div>
        {!! newstech_view_render_event('frontend.homepage.bottom', ['homepageLayout' => $homepageLayout]) !!}
    </div>
</x-newstech-frontend::layouts.app>
