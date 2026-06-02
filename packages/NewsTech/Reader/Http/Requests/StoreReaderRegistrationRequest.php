<?php

namespace NewsTech\Reader\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreReaderRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:readers,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
