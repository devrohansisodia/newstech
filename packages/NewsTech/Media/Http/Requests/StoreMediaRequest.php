<?php

namespace NewsTech\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use NewsTech\Core\Support\MediaManager;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var MediaManager $mediaManager */
        $mediaManager = app(MediaManager::class);

        return [
            'file' => [
                'required_without:files',
                ...$mediaManager->imageValidationRules(required: false),
            ],
            'files' => ['required_without:file', 'array'],
            'files.*' => $mediaManager->imageValidationRules(),
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
        ];
    }
}
