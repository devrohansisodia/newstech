@props([
    'name' => config('newstech.brand.name'),
    'tagline' => config('newstech.brand.tagline'),
    'logoPath' => config('newstech.brand.logo_path'),
    'useFooterLogo' => false,
    'tone' => 'light',
    'logoOnly' => false,
    'size' => 'default',
    'showTagline' => true,
])

@php
    $resolvedLogoPath = $useFooterLogo && filled(config('newstech.brand.footer_logo_path'))
        ? config('newstech.brand.footer_logo_path')
        : $logoPath;
    $resolvedLogoUrl = filled($resolvedLogoPath)
        ? app(\NewsTech\Core\Support\MediaManager::class)->url($resolvedLogoPath)
        : null;
    $accentClasses = $tone === 'dark'
        ? 'border-white/20 bg-white/10 text-white'
        : 'border-amber-300 bg-amber-50 text-amber-900';

    $taglineClasses = $tone === 'dark'
        ? 'text-slate-300'
        : 'text-stone-600';

    $imageClasses = $logoOnly
        ? 'h-14 max-w-[14rem] w-auto object-contain sm:h-16 sm:max-w-[16rem]'
        : ($size === 'prominent'
            ? 'h-9 max-w-[11rem] w-auto object-contain sm:h-10 sm:max-w-[13rem]'
            : 'h-7 max-w-[9rem] w-auto object-contain sm:max-w-[11rem]');
    $fallbackClasses = $logoOnly
        ? 'h-16 w-16 text-lg tracking-[0.28em] sm:h-18 sm:w-18'
        : ($size === 'prominent'
            ? 'h-12 w-12 text-base tracking-[0.28em] sm:h-13 sm:w-13'
            : 'h-11 w-11 text-sm tracking-[0.3em]');
    $nameToneClasses = $tone === 'dark'
        ? 'text-white'
        : 'text-stone-950';
    $nameClasses = $size === 'prominent'
        ? 'truncate text-xl font-semibold tracking-tight '.$nameToneClasses.' sm:text-2xl'
        : 'truncate text-lg font-semibold tracking-tight '.$nameToneClasses;
    $resolvedTaglineClasses = $size === 'prominent'
        ? $taglineClasses.' text-sm leading-5 sm:text-[0.95rem]'
        : $taglineClasses.' text-sm leading-6';
@endphp

<div class="space-y-2" data-brand-logo="{{ $resolvedLogoUrl ? 'custom' : 'fallback' }}">
    <div class="{{ $logoOnly ? 'flex w-full justify-center' : 'inline-flex' }} max-w-full items-center gap-3">
        @if ($resolvedLogoUrl)
            <span class="inline-flex max-w-full items-center rounded-xl border border-stone-200 bg-white px-3 py-2">
                <img src="{{ $resolvedLogoUrl }}" alt="{{ $name }} logo" class="{{ $imageClasses }}">
            </span>
        @else
            <span class="{{ $accentClasses }} {{ $fallbackClasses }} inline-flex items-center justify-center rounded-xl border font-black">
                NT
            </span>
        @endif

        @unless ($logoOnly)
            <div class="min-w-0" data-brand-copy>
                <p class="{{ $nameClasses }}">{{ $name }}</p>
                @if ($showTagline)
                    <p class="{{ $resolvedTaglineClasses }}">{{ $tagline }}</p>
                @endif
            </div>
        @endunless
    </div>
</div>
