<?php

namespace NewsTech\Reader\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReaderPasswordResetLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => str((string) $this->input('email'))->trim()->lower()->toString(),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
