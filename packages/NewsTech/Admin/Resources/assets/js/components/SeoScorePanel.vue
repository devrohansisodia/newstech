<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    analyzeUrl: {
        type: String,
        required: true,
    },
    csrfToken: {
        type: String,
        required: true,
    },
    enabled: {
        type: Boolean,
        default: true,
    },
    showSocialPreview: {
        type: Boolean,
        default: true,
    },
    scoreThresholdWarning: {
        type: Number,
        default: 80,
    },
});

const panelRoot = ref(null);
const analysis = ref(null);
const requestState = ref('idle');
const requestError = ref('');
const formElement = ref(null);
const cleanupCallbacks = [];
let debounceTimer = null;

const score = computed(() => analysis.value?.score ?? 0);
const grade = computed(() => analysis.value?.grade ?? 'poor');
const errors = computed(() => analysis.value?.errors ?? []);
const warnings = computed(() => analysis.value?.warnings ?? []);
const suggestions = computed(() => analysis.value?.suggestions ?? []);
const checklist = computed(() => analysis.value?.checklist ?? []);
const preview = computed(() => analysis.value?.preview ?? {
    title: '',
    url: '',
    description: '',
    social_title: '',
    social_description: '',
    social_image: null,
    canonical_url: '',
});
const scoreTone = computed(() => {
    if (score.value >= 80) {
        return {
            badge: 'bg-emerald-100 text-emerald-700',
            bar: 'bg-emerald-500',
        };
    }

    if (score.value >= 50) {
        return {
            badge: 'bg-amber-100 text-amber-700',
            bar: 'bg-amber-500',
        };
    }

    return {
        badge: 'bg-rose-100 text-rose-700',
        bar: 'bg-rose-500',
    };
});
const issueGroups = computed(() => ([
    {
        key: 'errors',
        label: 'Errors',
        items: errors.value,
        empty: 'No blocking SEO errors detected.',
        boxClass: 'border-rose-200 bg-rose-50',
        titleClass: 'text-rose-700',
        textClass: 'text-rose-900/90',
        badgeClass: 'bg-rose-100 text-rose-700',
        open: errors.value.length > 0,
    },
    {
        key: 'warnings',
        label: 'Warnings',
        items: warnings.value,
        empty: 'No major warnings detected.',
        boxClass: 'border-amber-200 bg-amber-50',
        titleClass: 'text-amber-700',
        textClass: 'text-amber-900/90',
        badgeClass: 'bg-amber-100 text-amber-700',
        open: warnings.value.length > 0,
    },
    {
        key: 'suggestions',
        label: 'Suggestions',
        items: suggestions.value,
        empty: 'No extra suggestions right now.',
        boxClass: 'border-sky-200 bg-sky-50',
        titleClass: 'text-sky-700',
        textClass: 'text-sky-900/90',
        badgeClass: 'bg-sky-100 text-sky-700',
        open: false,
    },
]));

function escapedName(name) {
    return name.replace(/"/g, '\\"');
}

function namedElements(name) {
    if (!(formElement.value instanceof HTMLFormElement)) {
        return [];
    }

    return Array.from(formElement.value.querySelectorAll(`[name="${escapedName(name)}"]`));
}

function firstElement(name) {
    return namedElements(name)[0] ?? null;
}

function fieldValue(name) {
    const elements = namedElements(name);

    if (elements.length === 0) {
        return '';
    }

    if (elements.length === 1) {
        const [element] = elements;

        if (element instanceof HTMLSelectElement && element.multiple) {
            return Array.from(element.selectedOptions).map((option) => option.value);
        }

        if (element instanceof HTMLInputElement && element.type === 'checkbox') {
            return element.checked;
        }

        return element.value ?? '';
    }

    const checkbox = elements.find((element) => element instanceof HTMLInputElement && element.type === 'checkbox');

    if (checkbox instanceof HTMLInputElement) {
        return checkbox.checked;
    }

    return elements.at(-1)?.value ?? '';
}

function selectedText(name) {
    const element = firstElement(name);

    if (!(element instanceof HTMLSelectElement)) {
        return '';
    }

    const option = element.selectedOptions[0];

    return option?.value ? option.textContent?.trim() ?? '' : '';
}

function selectedTexts(name) {
    const element = firstElement(name);

    if (!(element instanceof HTMLSelectElement) || !element.multiple) {
        return [];
    }

    return Array.from(element.selectedOptions)
        .map((option) => option.textContent?.trim() ?? '')
        .filter((value) => value !== '');
}

function payload() {
    return {
        type: props.type,
        title: fieldValue('title'),
        slug: fieldValue('slug'),
        excerpt: fieldValue('excerpt'),
        content_html: fieldValue('content'),
        meta_title: fieldValue('meta_title'),
        meta_description: fieldValue('meta_description'),
        featured_image: fieldValue('featured_image'),
        focus_keyword: fieldValue('focus_keyword'),
        status: fieldValue('status'),
        published_at: fieldValue('published_at'),
        author_name: selectedText('author_id'),
        category_name: '',
        tag_names: selectedTexts('tag_ids[]'),
    };
}

async function runAnalysis() {
    if (!props.enabled) {
        return;
    }

    requestState.value = 'loading';
    requestError.value = '';

    const response = await fetch(props.analyzeUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': props.csrfToken,
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload()),
    });

    if (!response.ok) {
        requestState.value = 'error';
        requestError.value = 'Live SEO analysis could not be refreshed right now.';

        return;
    }

    analysis.value = await response.json();
    requestState.value = 'ready';
}

function scheduleAnalysis() {
    if (!props.enabled) {
        return;
    }

    window.clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(() => {
        void runAnalysis();
    }, 350);
}

function registerFormListeners() {
    if (!(formElement.value instanceof HTMLFormElement)) {
        return;
    }

    [
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'featured_image',
        'focus_keyword',
        'status',
        'published_at',
        'author_id',
        'categories[]',
        'tag_ids[]',
    ].forEach((name) => {
        namedElements(name).forEach((element) => {
            const listener = () => scheduleAnalysis();

            element.addEventListener('input', listener);
            element.addEventListener('change', listener);

            cleanupCallbacks.push(() => {
                element.removeEventListener('input', listener);
                element.removeEventListener('change', listener);
            });
        });
    });
}

onMounted(() => {
    if (panelRoot.value instanceof HTMLElement) {
        formElement.value = panelRoot.value.closest('form');
    }

    registerFormListeners();
    scheduleAnalysis();
});

onBeforeUnmount(() => {
    window.clearTimeout(debounceTimer);
    cleanupCallbacks.splice(0).forEach((callback) => callback());
});
</script>

<template>
    <div ref="panelRoot" class="space-y-5 rounded-[1.75rem] border border-stone-200 bg-white p-5 text-stone-900 shadow-sm shadow-stone-200/70">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-600">Real-time SEO</p>
                <h3 class="text-xl font-black tracking-tight text-stone-950">SEO health</h3>
                <p class="max-w-2xl text-sm leading-6 text-stone-600">
                    Review metadata, search snippet quality, and social preview readiness before publishing.
                </p>
            </div>

            <div class="min-w-40 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-right">
                <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Score</p>
                <div class="mt-2 flex items-center justify-end gap-3">
                    <span class="text-4xl font-black tracking-tight text-stone-950">{{ score }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]" :class="scoreTone.badge">
                        {{ grade.replace('_', ' ') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <div class="h-2.5 overflow-hidden rounded-full bg-stone-200">
                <div class="h-full rounded-full transition-all duration-300" :class="scoreTone.bar" :style="{ width: `${score}%` }"></div>
            </div>

            <p class="text-xs text-stone-500">
                <span v-if="enabled && score < scoreThresholdWarning">This draft is still below the current warning threshold of {{ scoreThresholdWarning }}.</span>
                <span v-else-if="enabled">This draft is at or above the current warning threshold of {{ scoreThresholdWarning }}.</span>
                <span v-else>Real-time SEO checks are currently disabled in settings.</span>
            </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-4">
            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Errors</p>
                <p class="mt-2 text-2xl font-black text-stone-950">{{ errors.length }}</p>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Warnings</p>
                <p class="mt-2 text-2xl font-black text-stone-950">{{ warnings.length }}</p>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Suggestions</p>
                <p class="mt-2 text-2xl font-black text-stone-950">{{ suggestions.length }}</p>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Checks passed</p>
                <p class="mt-2 text-2xl font-black text-stone-950">{{ checklist.filter((item) => item.passed).length }}</p>
            </div>
        </div>

        <div v-if="requestState === 'error'" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ requestError }}
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
            <div class="space-y-4">
                <details
                    v-for="group in issueGroups"
                    :key="group.key"
                    class="rounded-2xl border border-stone-200 bg-stone-50"
                    :open="group.open"
                >
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-3 text-sm font-semibold text-stone-950">
                        <span>{{ group.label }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]" :class="group.badgeClass">
                            {{ group.items.length }}
                        </span>
                    </summary>

                    <div class="space-y-3 border-t border-stone-200 px-4 py-4">
                        <p v-if="group.items.length === 0" class="text-sm leading-6 text-stone-500">{{ group.empty }}</p>

                        <div
                            v-for="issue in group.items"
                            :key="issue.code"
                            class="rounded-2xl border px-4 py-3"
                            :class="group.boxClass"
                        >
                            <p class="font-semibold" :class="group.titleClass">{{ issue.title }}</p>
                            <p class="mt-1 text-sm leading-6 text-stone-700">{{ issue.message }}</p>
                            <p v-if="issue.recommendation" class="mt-2 text-sm leading-6" :class="group.textClass">{{ issue.recommendation }}</p>
                        </div>
                    </div>
                </details>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-stone-200 bg-white p-4 text-stone-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Snippet preview</p>
                    <div class="mt-4 space-y-1">
                        <p class="text-xl font-semibold leading-7 text-blue-700">{{ preview.title || 'Search title preview' }}</p>
                        <p class="text-sm text-emerald-700">{{ preview.url || 'https://example.com/preview' }}</p>
                        <p class="text-sm leading-6 text-stone-600">{{ preview.description || 'Search description preview appears here as you fill the form.' }}</p>
                    </div>
                </div>

                <div v-if="showSocialPreview" class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Social preview</p>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-stone-200 bg-white">
                        <div class="flex h-36 items-center justify-center bg-stone-200">
                            <img v-if="preview.social_image" :src="preview.social_image" alt="" class="h-full w-full object-cover">
                            <div v-else class="px-6 text-center text-sm leading-6 text-stone-500">
                                Select a featured image to improve social previews.
                            </div>
                        </div>

                        <div class="space-y-2 px-4 py-4 text-stone-900">
                            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-stone-500">Preview card</p>
                            <p class="text-lg font-semibold leading-7">{{ preview.social_title || 'Social title preview' }}</p>
                            <p class="text-sm leading-6 text-stone-600">{{ preview.social_description || 'Social description preview appears here.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Checklist</p>
                    <ul class="mt-3 space-y-3 text-sm leading-6">
                        <li v-for="item in checklist" :key="item.key" class="flex gap-3 rounded-2xl border border-stone-200 bg-white px-4 py-3">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full" :class="item.passed ? 'bg-emerald-500' : (item.severity === 'error' ? 'bg-rose-500' : (item.severity === 'warning' ? 'bg-amber-500' : 'bg-sky-500'))"></span>
                            <div>
                                <p class="font-semibold text-stone-950">{{ item.label }}</p>
                                <p class="text-stone-600">{{ item.message }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
