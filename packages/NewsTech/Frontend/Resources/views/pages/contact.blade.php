<x-newstech-frontend::page-shell
    :seo="$seo"
    :eyebrow="$pageEyebrow"
    :title="$pageTitle"
    :lead="$pageLead"
>
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.8fr)]">
        <x-newstech::panel class="space-y-4 border-white/10 bg-white/[0.05] p-6 text-stone-200">
            <h2 class="text-xl font-bold tracking-tight text-white">Editorial & Support Contacts</h2>
            <div class="space-y-3 text-sm leading-7 text-stone-300">
                <p><span class="font-semibold text-white">Editorial desk:</span> editorial@newstech.test</p>
                <p><span class="font-semibold text-white">Partnerships:</span> partnerships@newstech.test</p>
                <p><span class="font-semibold text-white">Support:</span> support@newstech.test</p>
                <p><span class="font-semibold text-white">Location:</span> NewsTech editorial workspace, India</p>
            </div>
        </x-newstech::panel>

        <x-newstech::panel class="space-y-4 border-white/10 bg-white/[0.05] p-6 text-stone-200">
            <h2 class="text-xl font-bold tracking-tight text-white">Response Note</h2>
            <p class="text-sm leading-7 text-stone-300">
                Contact form submission and ticketing remain intentionally deferred. Newsletter signup is now available from the frontend, but outreach and campaign sending are still not active.
            </p>
        </x-newstech::panel>
    </div>
</x-newstech-frontend::page-shell>
