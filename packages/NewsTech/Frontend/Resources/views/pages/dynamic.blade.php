<x-newstech-frontend::page-shell
    :seo="$seo"
    :eyebrow="$pageEyebrow"
    :title="$pageTitle"
    :lead="$pageLead"
>
    <x-newstech::panel class="space-y-5 border-stone-200 bg-white p-6 text-stone-700">
        @if ($page->content)
            <div class="nt-prose sm:text-lg" data-rich-content>
                {!! app(\NewsTech\Core\Support\RichTextContentRenderer::class)->render($page->content) !!}
            </div>
        @else
            <p class="text-sm leading-7 text-stone-600">
                This page is active in admin but does not have content yet.
            </p>
        @endif
    </x-newstech::panel>
</x-newstech-frontend::page-shell>
