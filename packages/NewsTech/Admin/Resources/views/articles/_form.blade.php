@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Article';
    $pageTitle ??= 'Article Form';
    $pageDescription ??= null;
    $formId = 'article-form';
    $seoScorePanelConfig = [
        'type' => 'article',
        'analyzeUrl' => route('admin.newstech.seo.analyze'),
        'csrfToken' => csrf_token(),
        'enabled' => (bool) config('newstech-seo.enable_real_time_checks'),
        'showSocialPreview' => (bool) config('newstech-seo.enable_social_preview'),
        'scoreThresholdWarning' => (int) config('newstech-seo.score_threshold_warning'),
    ];
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="NewsTech article form module."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button
                    :href="route('admin.newstech.articles.index')"
                    tone="ghost"
                >
                    Back to Articles
                </x-newstech-admin::form.button>

                <x-newstech-admin::form.button
                    type="submit"
                    tone="primary"
                    :form="$formId"
                >
                    {{ $submitLabel }}
                </x-newstech-admin::form.button>
            </x-slot:actions>
        </x-newstech-admin::page-header>

        <form id="{{ $formId }}" method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(20rem,0.9fr)]">
                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="General"
                    >
                        <x-newstech-admin::form.input
                            name="title"
                            label="Title"
                            :value="$article->title"
                            hint="Use the primary newsroom headline for this article."
                            placeholder="City Council Approves Emergency Budget"
                            data-seo-source="title"
                            required
                        />

                        <x-newstech-admin::form.slug-input
                            name="slug"
                            label="Slug"
                            :value="$article->slug"
                            prefix="/news/"
                            hint="Slug values are normalized server-side for future public article URLs."
                            data-seo-source="slug"
                            required
                        />

                        <x-newstech-admin::form.textarea
                            name="excerpt"
                            label="Excerpt"
                            :value="$article->excerpt"
                            hint="Short summary used for previews, cards, and future SEO snippets."
                            placeholder="Summarize the article in a few sentences."
                            data-seo-source="excerpt"
                            rows="4"
                        />

                        <x-newstech-admin::form.rich-text-editor
                            name="content"
                            label="Content"
                            :value="$article->content"
                            hint="Write the full article body and insert inline images where needed."
                            placeholder="Write the article body here."
                            data-seo-source="content"
                            rows="16"
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="SEO"
                        description="Search and social metadata with live quality checks."
                    >
                        <x-newstech-admin::form.input
                            name="meta_title"
                            label="Meta Title"
                            :value="$article->meta_title"
                            hint="Optional SEO title override for the public article page."
                            placeholder="Emergency Budget Approved | NewsTech"
                            data-seo-source="meta_title"
                        />

                        <x-newstech-admin::form.textarea
                            name="meta_description"
                            label="Meta Description"
                            :value="$article->meta_description"
                            hint="Optional description override for search and social previews."
                            placeholder="Article summary for search and social preview cards."
                            data-seo-source="meta_description"
                            rows="5"
                        />

                        <x-newstech-admin::form.input
                            name="focus_keyword"
                            label="Focus Keyword"
                            :value="$article->focus_keyword"
                            hint="Optional target phrase used by the real-time SEO panel to review keyword placement."
                            placeholder="emergency budget vote"
                            data-seo-source="focus_keyword"
                        />

                        <div
                            data-seo-score-panel-root="true"
                            data-vue-mount-status="pending"
                        >
                            <script type="application/json" data-seo-score-panel-config>
                                {!! json_encode($seoScorePanelConfig, JSON_THROW_ON_ERROR) !!}
                            </script>
                        </div>
                    </x-newstech-admin::form.section>

                </div>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Publishing Controls"
                        description="Status and timing controls for newsroom publishing."
                    >
                        <x-newstech-admin::form.select
                            name="status"
                            label="Status"
                            :options="NewsTech\Article\Models\Article::statusLabels()"
                            :value="$article->status"
                            placeholder="Choose a status"
                            hint="Draft, review, publish, schedule, or archive the article."
                            data-seo-source="status"
                            required
                        />

                        <x-newstech-admin::form.input
                            name="published_at"
                            label="Published At"
                            type="datetime-local"
                            :value="$article->published_at?->format('Y-m-d\TH:i')"
                            hint="Optional explicit publish timestamp. If status is Published and this is empty, the current time will be used."
                            data-seo-source="published_at"
                        />

                        <x-newstech-admin::form.input
                            name="scheduled_at"
                            label="Scheduled At"
                            type="datetime-local"
                            :value="$article->scheduled_at?->format('Y-m-d\TH:i')"
                            hint="Required when the article status is Scheduled."
                        />

                        <x-newstech-admin::form.toggle
                            name="is_featured"
                            label="Featured article"
                            :checked="$article->is_featured"
                        />

                        <x-newstech-admin::form.toggle
                            name="is_breaking"
                            label="Breaking article"
                            :checked="$article->is_breaking"
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Featured Image"
                        description="Main visual used for article cards and social sharing."
                    >
                        <x-newstech-admin::form.media-picker
                            name="featured_image"
                            label="Featured Image"
                            :value="$article->featured_image"
                            hint="Select or replace the main article image."
                            preview-label="Current featured image"
                            data-seo-source="featured_image"
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Content Relations"
                        description="Categories, author attribution, and tags."
                    >
                        <x-newstech-admin::form.field
                            label="Categories"
                            hint="Choose one or more categories. The first selected category remains the primary category."
                            :error="session('errors')?->first('categories')"
                        >
                            @if ($categoryTree->isNotEmpty())
                                <div class="rounded-2xl border border-stone-200 bg-white p-3">
                                    @include('newstech-admin::components.form.category-tree', [
                                        'categories' => $categoryTree,
                                        'selectedIds' => old('categories', $selectedCategoryIds),
                                    ])
                                </div>
                            @else
                                <div class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-sm text-stone-500">
                                    Create a category first to organize articles.
                                </div>
                            @endif
                        </x-newstech-admin::form.field>

                        <x-newstech-admin::form.select
                            name="author_id"
                            label="Author"
                            :options="$authorOptions"
                            :value="$article->author_id"
                            placeholder="Choose an author"
                            hint="Optional author or reporter assignment."
                        />

                        <x-newstech-admin::form.select
                            name="tag_ids[]"
                            error-name="tag_ids"
                            label="Tags"
                            :options="$tagOptions"
                            :value="$selectedTagIds"
                            :multiple="true"
                            hint="Choose one or more tags for discovery and filtering."
                        />
                    </x-newstech-admin::form.section>
                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
