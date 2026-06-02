<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Edit Media'"
    meta-description="Edit NewsTech media metadata."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-600">Media Library</p>
            <h2 class="text-3xl font-black tracking-tight text-stone-950">Edit media details</h2>
            <p class="max-w-3xl text-base leading-8 text-stone-600">
                Update the alt text and caption used to describe this reusable image asset.
            </p>
        </x-newstech::panel>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
            <form method="POST" action="{{ route('admin.newstech.media.update', $media) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <x-newstech-admin::form.section
                    title="Media Metadata"
                    description="Keep media descriptions lightweight and reusable across forms."
                >
                    <x-newstech-admin::form.input
                        name="alt_text"
                        label="Alt Text"
                        :value="$media->alt_text"
                        hint="Optional accessibility text for screen readers."
                    />

                    <x-newstech-admin::form.textarea
                        name="caption"
                        label="Caption"
                        :value="$media->caption"
                        rows="5"
                        hint="Optional editorial or internal caption."
                    />

                    <x-newstech-admin::form.actions>
                        <x-newstech-admin::form.button :href="route('admin.newstech.media.index')" tone="ghost">
                            Back to Media
                        </x-newstech-admin::form.button>

                        <x-newstech-admin::form.button type="submit" tone="primary">
                            Save Media Details
                        </x-newstech-admin::form.button>
                    </x-newstech-admin::form.actions>
                </x-newstech-admin::form.section>
            </form>

            <x-newstech-admin::form.section
                title="Preview"
                description="Reference information for the selected media asset."
            >
                <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-5">
                    <img src="{{ $previewUrl }}" alt="{{ $media->alt_text ?: $media->filename }}" class="max-h-64 w-full rounded-xl border border-stone-200 bg-stone-50 object-contain">

                    <dl class="space-y-2 text-sm text-stone-600">
                        <div>
                            <dt class="font-semibold text-stone-900">Filename</dt>
                            <dd>{{ $media->original_name ?: $media->filename }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-stone-900">Stored Path</dt>
                            <dd class="break-all">{{ $media->path }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-stone-900">Mime Type</dt>
                            <dd>{{ $media->mime_type ?: 'Unknown type' }}</dd>
                        </div>
                    </dl>
                </div>
            </x-newstech-admin::form.section>
        </div>
    </div>
</x-newstech-admin::layouts.app>
