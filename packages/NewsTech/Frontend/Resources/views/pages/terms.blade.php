<x-newstech-frontend::page-shell
    :seo="$seo"
    :eyebrow="$pageEyebrow"
    :title="$pageTitle"
    :lead="$pageLead"
>
    <x-newstech::panel class="space-y-5 border-white/10 bg-white/[0.05] p-6 text-stone-200">
        <div class="space-y-3">
            <h2 class="text-xl font-bold tracking-tight text-white">Content Access</h2>
            <p class="text-sm leading-7 text-stone-300">
                Public NewsTech pages are provided for lawful reading, discovery, and sharing of published editorial content.
            </p>
        </div>

        <div class="space-y-3">
            <h2 class="text-xl font-bold tracking-tight text-white">Acceptable Use</h2>
            <p class="text-sm leading-7 text-stone-300">
                Users should not misuse the website, interfere with availability, or attempt to automate abusive access against NewsTech pages or search endpoints.
            </p>
        </div>

        <div class="space-y-3">
            <h2 class="text-xl font-bold tracking-tight text-white">Editorial Rights</h2>
            <p class="text-sm leading-7 text-stone-300">
                NewsTech may revise, archive, or remove content and policies as the platform evolves from foundation work into a fuller public news product.
            </p>
        </div>
    </x-newstech::panel>
</x-newstech-frontend::page-shell>
