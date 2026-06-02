<?php

namespace NewsTech\Newsletter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use NewsTech\Newsletter\Models\NewsletterSubscriber;

class UpdateNewsletterSubscriberRequest extends FormRequest
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
        return [
            'status' => ['required', Rule::in([
                NewsletterSubscriber::STATUS_ACTIVE,
                NewsletterSubscriber::STATUS_UNSUBSCRIBED,
                NewsletterSubscriber::STATUS_INACTIVE,
            ])],
            'source' => ['nullable', 'string', 'max:255'],
        ];
    }
}
