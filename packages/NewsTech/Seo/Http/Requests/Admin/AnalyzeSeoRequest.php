<?php

namespace NewsTech\Seo\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth(config('newstech-admin.auth.guard'))->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->filled('type') ? (string) $this->input('type') : 'article',
            'status' => is_bool($this->input('status')) ? $this->boolean('status') : $this->input('status'),
            'tag_names' => collect($this->input('tag_names', []))
                ->filter(fn ($tagName): bool => is_string($tagName) && trim($tagName) !== '')
                ->map(fn ($tagName): string => trim((string) $tagName))
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:article,page'],
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content_html' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'focus_keyword' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:2048'],
            'status' => ['nullable'],
            'published_at' => ['nullable', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'tag_names' => ['nullable', 'array'],
            'tag_names.*' => ['string', 'max:255'],
        ];
    }
}
