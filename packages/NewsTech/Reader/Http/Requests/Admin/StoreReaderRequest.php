<?php

namespace NewsTech\Reader\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreReaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => str((string) $this->input('name'))->trim()->toString(),
            'email' => str((string) $this->input('email'))->trim()->lower()->toString(),
            'website' => str((string) $this->input('website'))->trim()->toString() ?: null,
            'bio' => str((string) $this->input('bio'))->trim()->toString() ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:readers,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'is_active' => ['required', 'boolean'],
            'website' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
