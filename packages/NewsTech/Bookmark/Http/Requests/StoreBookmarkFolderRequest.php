<?php

namespace NewsTech\Bookmark\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookmarkFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth(config('newstech-reader.auth.guard'))->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => str((string) $this->input('name'))->trim()->toString(),
        ]);
    }

    public function rules(): array
    {
        $reader = $this->user(config('newstech-reader.auth.guard'));

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('bookmark_folders', 'name')->where(fn ($query) => $query->where('reader_id', $reader?->getKey())),
            ],
        ];
    }
}
