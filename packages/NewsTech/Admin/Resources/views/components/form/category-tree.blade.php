@props([
    'categories',
    'selectedIds' => [],
    'level' => 0,
])

<div class="space-y-3">
    @foreach ($categories as $category)
        @php
            $isChecked = in_array($category->getKey(), $selectedIds, true);
        @endphp

        <div class="space-y-2">
            <label
                class="flex items-start gap-3 rounded-xl px-3 py-2 text-sm text-stone-700 transition hover:bg-stone-50"
                @if ($level > 0) style="margin-left: {{ $level * 1.25 }}rem" @endif
            >
                <input
                    type="checkbox"
                    name="categories[]"
                    value="{{ $category->getKey() }}"
                    @checked($isChecked)
                    class="mt-1 h-4 w-4 rounded border-stone-300 text-amber-600 focus:ring-amber-300"
                >

                <span class="block font-semibold text-stone-950">{{ $category->name }}</span>
            </label>

            @if ($category->childrenRecursive->isNotEmpty())
                @include('newstech-admin::components.form.category-tree', [
                    'categories' => $category->childrenRecursive,
                    'selectedIds' => $selectedIds,
                    'level' => $level + 1,
                ])
            @endif
        </div>
    @endforeach
</div>
