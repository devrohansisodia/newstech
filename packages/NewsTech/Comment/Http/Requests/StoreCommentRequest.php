<?php

namespace NewsTech\Comment\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $honeypotField = (string) config('newstech-comment.honeypot_field', 'company');
        $websiteFieldEnabled = config('newstech-comment.website_field_enabled', true);

        $this->merge([
            'name' => str((string) $this->input('name'))->trim()->toString(),
            'email' => str((string) $this->input('email'))->trim()->lower()->toString(),
            'website' => $websiteFieldEnabled ? (str((string) $this->input('website'))->trim()->toString() ?: null) : null,
            'content' => str((string) $this->input('content'))->trim()->toString(),
            $honeypotField => str((string) $this->input($honeypotField))->trim()->toString(),
        ]);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $honeypotField = (string) config('newstech-comment.honeypot_field', 'company');
        $websiteFieldEnabled = config('newstech-comment.website_field_enabled', true);
        $minLength = max(1, (int) config('newstech-comment.min_comment_length', 5));
        $maxLength = max($minLength, (int) config('newstech-comment.max_comment_length', 2000));

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'website' => $websiteFieldEnabled
                ? ['nullable', 'url', 'max:255']
                : ['nullable'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'content' => ['required', 'string', 'min:'.$minLength, 'max:'.$maxLength],
            $honeypotField => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'email address',
            'content' => 'comment',
        ];
    }
}
