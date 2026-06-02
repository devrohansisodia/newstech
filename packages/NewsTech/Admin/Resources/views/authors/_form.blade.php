@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Author';
    $pageTitle ??= 'Author Form';
    $pageDescription ??= null;
    $formId = 'author-form';
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="NewsTech author form module."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.authors.index')" tone="ghost">
                    Back to Authors
                </x-newstech-admin::form.button>

                <x-newstech-admin::form.button type="submit" tone="primary" :form="$formId">
                    {{ $submitLabel }}
                </x-newstech-admin::form.button>
            </x-slot:actions>
        </x-newstech-admin::page-header>

        <p class="max-w-3xl text-sm font-medium leading-7 text-stone-700">
            Authors are public content bylines and reporters, not admin login users.
        </p>

        <form id="{{ $formId }}" method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(18rem,0.9fr)]">
                <div class="space-y-6">
                    <x-newstech-admin::form.section title="General">
                        <x-newstech-admin::form.input
                            name="name"
                            label="Name"
                            :value="$author->name"
                            hint="Use the public byline or reporter name that should appear across future content."
                            placeholder="Aarav Mehta"
                            required
                        />

                        <x-newstech-admin::form.slug-input
                            name="slug"
                            label="Slug"
                            :value="$author->slug"
                            prefix="/authors/"
                            hint="Slug values are normalized server-side to keep future author URLs clean."
                            required
                        />

                        <x-newstech-admin::form.input
                            name="email"
                            label="Email"
                            type="email"
                            :value="$author->email"
                            hint="Optional internal or public contact email for the author profile."
                            placeholder="reporter@newstech.test"
                        />

                        <x-newstech-admin::form.input
                            name="designation"
                            label="Designation"
                            :value="$author->designation"
                            hint="Optional newsroom role such as Senior Reporter or Political Editor."
                            placeholder="Senior Reporter"
                        />

                        <x-newstech-admin::form.textarea
                            name="bio"
                            label="Bio"
                            :value="$author->bio"
                            hint="Short profile summary for future byline panels and public author pages."
                            placeholder="Reporter bio and editorial background."
                            rows="6"
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Social Profiles"
                        description="Optional public profile links that can later power byline cards and author pages."
                    >
                        <x-newstech-admin::form.input
                            name="facebook_url"
                            label="Facebook URL"
                            type="url"
                            :value="$author->facebook_url"
                            placeholder="https://facebook.com/author-profile"
                        />

                        <x-newstech-admin::form.input
                            name="twitter_url"
                            label="Twitter URL"
                            type="url"
                            :value="$author->twitter_url"
                            placeholder="https://x.com/author-profile"
                        />

                        <x-newstech-admin::form.input
                            name="linkedin_url"
                            label="LinkedIn URL"
                            type="url"
                            :value="$author->linkedin_url"
                            placeholder="https://linkedin.com/in/author-profile"
                        />

                        <x-newstech-admin::form.input
                            name="website_url"
                            label="Website URL"
                            type="url"
                            :value="$author->website_url"
                            placeholder="https://author-portfolio.example"
                        />
                    </x-newstech-admin::form.section>
                </div>

                <div class="space-y-6">
                    <x-newstech-admin::form.section title="Publishing Controls">
                        <x-newstech-admin::form.toggle
                            name="status"
                            label="Active status"
                            :checked="$author->status"
                            hint="Inactive profiles remain stored but can be excluded from future byline selection."
                        />

                        <x-newstech-admin::form.media-picker
                            name="avatar"
                            label="Avatar"
                            :value="$author->avatar"
                            hint="Select an existing image or upload a new one from the shared media library."
                            preview-label="Current avatar"
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section title="SEO">
                        <x-newstech-admin::form.input
                            name="meta_title"
                            label="Meta Title"
                            :value="$author->meta_title"
                            hint="Optional SEO title override for future public author pages."
                            placeholder="Aarav Mehta | NewsTech"
                        />

                        <x-newstech-admin::form.textarea
                            name="meta_description"
                            label="Meta Description"
                            :value="$author->meta_description"
                            hint="Optional description override for future search and social previews."
                            placeholder="Latest reporting and profile information for this NewsTech author."
                            rows="5"
                        />
                    </x-newstech-admin::form.section>
                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
