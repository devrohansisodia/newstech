@php
    $advertisementQuery = \NewsTech\Advertisement\Models\Advertisement::query();
@endphp

<x-newstech-admin::stat-card
    eyebrow="Ads"
    title="Active Ads"
    :value="$advertisementQuery->where('status', \NewsTech\Advertisement\Models\Advertisement::STATUS_ACTIVE)->count()"
    :description="'Total impressions: '.$advertisementQuery->sum('impressions_count').' · clicks: '.$advertisementQuery->sum('clicks_count')"
/>
