<?php

namespace NewsTech\Menu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use NewsTech\Menu\Models\MenuItem;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort_order' => (int) ($this->input('sort_order') ?: 0),
            'status' => $this->boolean('status'),
            'opens_in_new_tab' => $this->boolean('opens_in_new_tab'),
            'parent_id' => $this->filled('parent_id') ? (int) $this->input('parent_id') : null,
            'page_id' => $this->filled('page_id') ? (int) $this->input('page_id') : null,
            'category_id' => $this->filled('category_id') ? (int) $this->input('category_id') : null,
            'url' => $this->filled('url') ? trim((string) $this->input('url')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(array_keys(MenuItem::typeOptions()))],
            'url' => ['nullable', 'string', 'max:2048', Rule::requiredIf($this->input('type') === 'custom_url')],
            'page_id' => ['nullable', 'integer', 'exists:pages,id', Rule::requiredIf($this->input('type') === 'page')],
            'category_id' => ['nullable', 'integer', 'exists:categories,id', Rule::requiredIf($this->input('type') === 'category')],
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'opens_in_new_tab' => ['nullable', 'boolean'],
        ];
    }
}
