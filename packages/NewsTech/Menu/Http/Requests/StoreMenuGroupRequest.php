<?php

namespace NewsTech\Menu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use NewsTech\Menu\Models\MenuGroup;

class StoreMenuGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', Rule::in(array_keys(MenuGroup::locationOptions()))],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
