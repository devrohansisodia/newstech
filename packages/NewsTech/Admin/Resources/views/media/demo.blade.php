<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Media Demo'"
    meta-description="NewsTech Phase 1.9 reusable media upload foundation."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-white/10 bg-white/5 p-8 text-slate-100 shadow-black/20">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-sky-300">Phase 1.9</p>
            <h2 class="text-3xl font-black tracking-tight text-white">Media upload foundation is ready for future editorial modules.</h2>
            <p class="max-w-3xl text-base leading-8 text-slate-300">
                This demo proves the shared upload helper, storage path resolution, public URL generation, and safe deletion support without introducing a full media library yet.
            </p>
        </x-newstech::panel>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
            <form
                method="POST"
                action="{{ route('admin.newstech.foundation.media-demo.store') }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf

                <x-newstech-admin::form.section
                    title="Upload Demo"
                    description="Uses the shared Core media manager and existing admin form foundation."
                >
                    <x-newstech-admin::form.file-input
                        name="upload"
                        label="Upload file"
                        hint="Accepts configured image types only in this phase. Files are stored on the configured media disk and path."
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    />

                    <x-newstech-admin::form.actions>
                        <x-newstech-admin::form.button type="submit" tone="primary">
                            Upload Demo File
                        </x-newstech-admin::form.button>
                    </x-newstech-admin::form.actions>
                </x-newstech-admin::form.section>
            </form>

            <x-newstech-admin::form.section
                title="Stored Result"
                description="Successful uploads surface the stored disk, path, and resolved public URL."
            >
                @php
                    $upload = session('media_demo_upload');
                @endphp

                @if ($upload)
                    <div class="space-y-4 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-5 text-sm text-emerald-100">
                        <div>
                            <p class="font-semibold uppercase tracking-[0.2em]">Disk</p>
                            <p class="mt-1 break-all">{{ $upload['disk'] }}</p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase tracking-[0.2em]">Stored Path</p>
                            <p class="mt-1 break-all">{{ $upload['path'] }}</p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase tracking-[0.2em]">Public URL</p>
                            <a href="{{ $upload['url'] }}" class="mt-1 block break-all text-sky-200 hover:text-sky-100">
                                {{ $upload['url'] }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-white/10 bg-slate-950/60 p-5 text-sm leading-7 text-slate-400">
                        No file has been uploaded in this demo yet.
                    </div>
                @endif
            </x-newstech-admin::form.section>
        </div>
    </div>
</x-newstech-admin::layouts.app>
