<?php

namespace NewsTech\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth(config('newstech-admin.auth.guard'))->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => str((string) $this->input('name'))->trim()->toString(),
            'email' => str((string) $this->input('email'))->trim()->lower()->toString(),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $adminUser = $this->user(config('newstech-admin.auth.guard'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admin_users', 'email')->ignore($adminUser?->getKey())],
            'current_password' => ['nullable', 'required_with:password', 'current_password:admin'],
            'password' => ['nullable', 'required_with:current_password', 'confirmed', Password::min(8)],
        ];
    }
}
