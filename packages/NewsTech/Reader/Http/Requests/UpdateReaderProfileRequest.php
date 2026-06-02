<?php

namespace NewsTech\Reader\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateReaderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth(config('newstech-reader.auth.guard'))->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => str((string) $this->input('name'))->trim()->toString(),
            'email' => str((string) $this->input('email'))->trim()->lower()->toString(),
            'website' => str((string) $this->input('website'))->trim()->toString() ?: null,
            'bio' => str((string) $this->input('bio'))->trim()->toString() ?: null,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $reader = $this->user(config('newstech-reader.auth.guard'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('readers', 'email')->ignore($reader?->getKey())],
            'website' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];
    }
}
