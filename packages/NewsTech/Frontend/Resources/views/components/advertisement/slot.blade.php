@props([
    'key',
    'compact' => false,
])

@include('newstech-advertisement::placeholder', [
    'key' => $key,
    'compact' => $compact,
    'attributes' => $attributes,
])
