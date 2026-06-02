@props([
    'message' => null,
])

@if ($message)
    <p {{ $attributes->class('text-sm font-medium text-rose-600') }}>
        {{ $message }}
    </p>
@endif
