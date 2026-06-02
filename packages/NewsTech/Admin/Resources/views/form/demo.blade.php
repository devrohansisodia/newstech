<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Form Demo'"
    meta-description="NewsTech Phase 1.6 reusable admin form components foundation."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-white/10 bg-white/5 p-8 text-slate-100 shadow-black/20">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-sky-300">Phase 1.6</p>
            <h2 class="text-3xl font-black tracking-tight text-white">Admin form foundation is ready for future editing modules.</h2>
            <p class="max-w-3xl text-base leading-8 text-slate-300">
                This demo stays read-only from a data perspective. It only previews reusable Blade form fields, validation rendering, and old-input handling that upcoming content modules can share.
            </p>

            @if (session('form_demo_status'))
                <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('form_demo_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <form method="POST" action="{{ route('admin.newstech.foundation.form-demo.preview') }}" class="space-y-6">
            @csrf

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(18rem,0.9fr)]">
                <x-newstech-admin::form.section
                    title="Editorial Basics"
                    description="Core input components for titles, slugs, summaries, and select fields."
                >
                    <x-newstech-admin::form.input
                        name="title"
                        label="Title"
                        value="Weekend Market Rally Extends Into Regional Exchanges"
                        hint="Use a concise, search-friendly headline. Old input will replace this default after a preview submit."
                        placeholder="Enter a working headline"
                        required
                    />

                    <x-newstech-admin::form.slug-input
                        name="slug"
                        label="Slug"
                        value="weekend-market-rally-extends-into-regional-exchanges"
                        hint="This placeholder slug field is intended for future auto-generation and manual edits."
                        required
                    />

                    <x-newstech-admin::form.textarea
                        name="excerpt"
                        label="Excerpt"
                        value="Regional exchanges closed higher after a weekend rally, giving editors a compact summary field to reuse across listings, cards, and SEO snippets."
                        hint="Reusable summary field for cards, previews, and future SEO descriptions."
                        rows="6"
                        required
                    />

                    <x-newstech-admin::form.select
                        name="section"
                        label="Section"
                        :options="[
                            'politics' => 'Politics',
                            'business' => 'Business',
                            'culture' => 'Culture',
                        ]"
                        value="business"
                        hint="Future modules can swap these static options for config or API-backed values."
                        placeholder="Choose a section"
                        required
                    />
                </x-newstech-admin::form.section>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Publishing Controls"
                        description="Small reusable controls for flags, media placeholders, and side-panel settings."
                    >
                        <x-newstech-admin::form.toggle
                            name="is_featured"
                            label="Featured placement"
                            :checked="true"
                            hint="Toggle-style checkbox foundation for future featured, breaking, or sponsored flags."
                        />

                        <x-newstech-admin::form.file-placeholder
                            name="featured_image"
                            label="Featured image"
                            hint="Uploads are intentionally deferred. This only reserves the form pattern."
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Form Actions"
                        description="Buttons are component-based so future CRUD modules can keep footer actions visually consistent."
                    >
                        <x-newstech-admin::form.actions>
                            <x-newstech-admin::form.button
                                :href="route('admin.newstech.dashboard')"
                                tone="ghost"
                            >
                                Back to Dashboard
                            </x-newstech-admin::form.button>

                            <x-newstech-admin::form.button type="submit" tone="primary">
                                Preview Form State
                            </x-newstech-admin::form.button>
                        </x-newstech-admin::form.actions>
                    </x-newstech-admin::form.section>
                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
