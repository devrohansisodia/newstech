<?php

namespace NewsTech\Advertisement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use NewsTech\Advertisement\Models\Advertisement;
use NewsTech\Advertisement\Support\AdvertisementSlotManager;

class StoreAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => filled($this->input('slug')) ? str((string) $this->input('slug'))->slug()->toString() : null,
            'open_in_new_tab' => $this->boolean('open_in_new_tab'),
            'nofollow' => $this->boolean('nofollow'),
            'sponsored' => $this->boolean('sponsored'),
            'priority' => $this->input('priority', 0),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $slotManager = app(AdvertisementSlotManager::class);

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('advertisements', 'slug')],
            'type' => ['required', Rule::in(array_keys(Advertisement::typeOptions()))],
            'status' => ['required', Rule::in(array_keys(Advertisement::statusOptions()))],
            'slot_key' => ['required', Rule::in($slotManager->keys())],
            'title' => ['nullable', 'string', 'max:255'],
            'image_path' => ['nullable', 'string', 'max:2048'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'html_content' => ['nullable', 'string'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'nofollow' => ['nullable', 'boolean'],
            'sponsored' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('type') === Advertisement::TYPE_IMAGE && ! filled($this->input('image_path'))) {
                $validator->errors()->add('image_path', 'Select an image for image advertisements.');
            }

            if ($this->input('type') === Advertisement::TYPE_HTML && ! filled($this->input('html_content'))) {
                $validator->errors()->add('html_content', 'Enter trusted HTML or code content for HTML advertisements.');
            }

            if (filled($this->input('starts_at')) && filled($this->input('ends_at'))) {
                $startsAt = strtotime((string) $this->input('starts_at'));
                $endsAt = strtotime((string) $this->input('ends_at'));

                if ($startsAt !== false && $endsAt !== false && $endsAt <= $startsAt) {
                    $validator->errors()->add('ends_at', 'The end date must be after the start date.');
                }
            }
        });
    }
}
