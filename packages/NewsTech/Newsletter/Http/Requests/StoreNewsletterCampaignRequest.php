<?php

namespace NewsTech\Newsletter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use NewsTech\Newsletter\Models\NewsletterCampaign;

class StoreNewsletterCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', NewsletterCampaign::STATUS_DRAFT),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in([
                NewsletterCampaign::STATUS_DRAFT,
                NewsletterCampaign::STATUS_SCHEDULED,
            ])],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}
