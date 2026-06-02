@props([
    'name',
    'label',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'rows' => 12,
    'required' => false,
])

@php
    $fieldId = str($name)->replace(['[', ']', '.'], '-');
    $fieldError = session('errors')?->first($name);
    $resolvedValue = old($name, $value);
@endphp

<x-newstech-admin::form.field
    :for="$fieldId"
    :label="$label"
    :hint="$hint"
    :error="$fieldError"
    :required="$required"
>
    <div class="space-y-4" data-rich-text-editor>
        <div
            class="hidden overflow-hidden rounded-[1.5rem] border border-stone-200 bg-white shadow-sm"
            data-rich-text-editor-ui
        >
            <div
                class="flex flex-wrap gap-2 border-b border-stone-200 bg-stone-50/80 px-4 py-3"
                data-rich-text-editor-toolbar
                aria-label="{{ $label }} formatting toolbar"
            >
                @php
                    $toolbarButtons = [
                        ['action' => 'paragraph', 'label' => 'Paragraph'],
                        ['action' => 'heading', 'label' => 'H2', 'level' => '2'],
                        ['action' => 'heading', 'label' => 'H3', 'level' => '3'],
                        ['action' => 'bold', 'label' => 'Bold'],
                        ['action' => 'italic', 'label' => 'Italic'],
                        ['action' => 'bullet-list', 'label' => 'Bullet List'],
                        ['action' => 'ordered-list', 'label' => 'Ordered List'],
                        ['action' => 'blockquote', 'label' => 'Quote'],
                        ['action' => 'link', 'label' => 'Link'],
                        ['action' => 'image', 'label' => 'Insert Image'],
                        ['action' => 'clear-formatting', 'label' => 'Clear'],
                        ['action' => 'undo', 'label' => 'Undo'],
                        ['action' => 'redo', 'label' => 'Redo'],
                    ];
                @endphp

                @foreach ($toolbarButtons as $button)
                    <button
                        type="button"
                        class="nt-rich-text-editor-button"
                        data-rich-text-editor-action="{{ $button['action'] }}"
                        @if ($button['action'] === 'image') data-rich-text-editor-image-picker-open @endif
                        @if (isset($button['level'])) data-rich-text-editor-level="{{ $button['level'] }}" @endif
                        aria-pressed="false"
                    >
                        {{ $button['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="nt-rich-text-editor-surface" data-rich-text-editor-content></div>
        </div>

        <textarea
            id="{{ $fieldId }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            @required($required)
            data-rich-text-editor-source
            {{ $attributes->class([
                'w-full rounded-xl border bg-white px-4 py-3 text-sm leading-7 text-stone-700 placeholder:text-stone-400 focus:outline-none',
                'border-rose-300 focus:border-rose-500' => $fieldError,
                'border-stone-200 focus:border-amber-400' => ! $fieldError,
            ]) }}
        >{{ $resolvedValue }}</textarea>
    </div>
</x-newstech-admin::form.field>
