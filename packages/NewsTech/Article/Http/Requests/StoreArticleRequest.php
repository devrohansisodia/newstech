<?php

namespace NewsTech\Article\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use NewsTech\Article\Models\Article;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $categoryIds = collect($this->input('categories', []))
            ->filter(fn ($categoryId) => filled($categoryId))
            ->map(fn ($categoryId) => (int) $categoryId)
            ->unique()
            ->values()
            ->all();

        $this->merge([
            'categories' => $categoryIds,
            'category_id' => $categoryIds[0] ?? null,
            'author_id' => $this->filled('author_id') ? (int) $this->input('author_id') : null,
            'slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : null,
            'status' => $this->filled('status') ? (string) $this->input('status') : 'draft',
            'is_featured' => $this->boolean('is_featured'),
            'is_breaking' => $this->boolean('is_breaking'),
            'published_at' => $this->filled('published_at') ? (string) $this->input('published_at') : null,
            'scheduled_at' => $this->filled('scheduled_at') ? (string) $this->input('scheduled_at') : null,
            'tag_ids' => collect($this->input('tag_ids', []))
                ->filter(fn ($tagId) => filled($tagId))
                ->map(fn ($tagId) => (int) $tagId)
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
            'category_id' => ['nullable', 'exists:categories,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'author_id' => ['nullable', 'exists:authors,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:articles,slug'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(Article::statusLabels()))],
            'is_featured' => ['nullable', 'boolean'],
            'is_breaking' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date', Rule::requiredIf(fn () => $this->input('status') === 'scheduled')],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'focus_keyword' => ['nullable', 'string', 'max:255'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
