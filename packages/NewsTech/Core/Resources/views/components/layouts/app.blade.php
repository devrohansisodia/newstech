@props([
    'title' => config('newstech.meta.default_title'),
    'metaDescription' => config('newstech.meta.default_description'),
    'bodyClass' => 'bg-stone-50 text-stone-900',
    'seo' => null,
    'viteEntries' => [],
    'viteBuildDirectory' => 'build',
    'viteHotFile' => 'hot',
    'viteManifestFilename' => 'manifest.json',
])

@php
    use Illuminate\Support\Facades\Vite;

    $resolvedSeo = $seo instanceof \NewsTech\Core\Support\SeoData
        ? $seo
        : \NewsTech\Core\Support\SeoData::make(
            $title,
            $metaDescription,
            url()->current()
        );

    $resolvedViteEntries = array_values(array_filter((array) $viteEntries));
    $resolvedBuildDirectory = trim($viteBuildDirectory, '/');
    $resolvedHotFile = ltrim($viteHotFile, '/');
    $resolvedManifestFilename = $viteManifestFilename;

    $packageHotFilePath = public_path($resolvedHotFile);
    $packageManifestPath = public_path($resolvedBuildDirectory.'/'.$resolvedManifestFilename);
    $defaultHotFilePath = public_path('hot');
    $defaultManifestPath = public_path('build/manifest.json');

    $packageAssetsAvailable = file_exists($packageHotFilePath) || file_exists($packageManifestPath);
    $defaultAssetsAvailable = file_exists($defaultHotFilePath) || file_exists($defaultManifestPath);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-newstech::seo.meta :seo="$resolvedSeo" />
        {{ $head ?? '' }}

        @if ($resolvedViteEntries !== [])
            @if ($packageAssetsAvailable)
                {{
                    Vite::useHotFile($packageHotFilePath)
                        ->useBuildDirectory($resolvedBuildDirectory)
                        ->useManifestFilename($resolvedManifestFilename)
                        ->withEntryPoints($resolvedViteEntries)
                }}
            @elseif ($defaultAssetsAvailable)
                @vite($resolvedViteEntries)
            @endif
        @endif
    </head>

    <body {{ $attributes->class([$bodyClass]) }}>
        {{ $slot }}
    </body>
</html>
