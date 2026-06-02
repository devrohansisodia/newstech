@props([
    'source',
    'title' => 'Subscribe to the newsletter',
    'description' => 'Get the latest published NewsTech stories in your inbox once newsletter sending is enabled.',
    'compact' => false,
])

@php
    $feedbackSource = old('source') ?: session('newsletter_source');
    $showFeedback = $feedbackSource === $source;
    $statusTone = session('newsletter_status_tone', 'success');
    $statusClasses = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
    ];
    $emailError = $showFeedback ? $errors->first('email') : null;
@endphp

@if (config('newstech-newsletter.enabled', true))
    <x-newstech::panel
        {{ $attributes->class([
            'space-y-4 border-stone-200 bg-white text-stone-700 shadow-sm shadow-stone-200/60',
            'p-5' => $compact,
            'p-6 sm:p-7' => ! $compact,
        ]) }}
    >
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-600">Newsletter</p>
            <h2 @class([
                'font-black tracking-tight text-stone-950',
                'text-xl' => $compact,
                'text-2xl' => ! $compact,
            ])>{{ $title }}</h2>
            <p class="text-sm leading-7 text-stone-600">{{ $description }}</p>
        </div>

        @if ($showFeedback && session('newsletter_status'))
            <div class="rounded-xl border px-4 py-3 text-sm {{ $statusClasses[$statusTone] ?? $statusClasses['success'] }}">
                {{ session('newsletter_status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('newstech.newsletter.subscribe') }}" class="space-y-3" aria-label="Newsletter subscription form">
            @csrf

            <input type="hidden" name="source" value="{{ $source }}">

            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="flex-1 space-y-2">
                    <label for="newsletter-email-{{ $source }}" class="sr-only">Email address</label>
                    <input
                        id="newsletter-email-{{ $source }}"
                        type="email"
                        name="email"
                        value="{{ old('source') === $source ? old('email') : '' }}"
                        placeholder="Enter your email address"
                        autocomplete="email"
                        class="w-full rounded-xl border bg-stone-50 px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/50 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100 {{ $emailError ? 'border-rose-300 focus:border-rose-500' : 'border-stone-200 focus:border-amber-300' }}"
                    >

                    @if ($emailError)
                        <p class="text-sm text-rose-600">{{ $emailError }}</p>
                    @endif
                </div>

                <button
                    type="submit"
                    class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/50 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100"
                >
                    Subscribe
                </button>
            </div>
        </form>
    </x-newstech::panel>
@endif
