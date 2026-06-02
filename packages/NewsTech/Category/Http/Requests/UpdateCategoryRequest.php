<?php

namespace NewsTech\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use NewsTech\Category\Models\Category;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'parent_id' => $this->filled('parent_id') ? (int) $this->input('parent_id') : null,
            'slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : null,
            'status' => $this->boolean('status'),
            'sort_order' => $this->filled('sort_order') ? (int) $this->input('sort_order') : 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->currentCategory();

        return [
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($category?->getKey()),
            ],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $category = $this->currentCategory();

                if ($category !== null && $this->integer('parent_id') === (int) $category->getKey()) {
                    $validator->errors()->add('parent_id', 'A category cannot be its own parent.');
                }
            },
        ];
    }

    protected function currentCategory(): ?Category
    {
        $routeCategory = $this->route('category');

        if ($routeCategory instanceof Category) {
            return $routeCategory;
        }

        if (is_numeric($routeCategory)) {
            return Category::query()->find((int) $routeCategory);
        }

        return null;
    }
}
