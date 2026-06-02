<?php

namespace NewsTech\Bookmark\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookmarkFolderAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth(config('newstech-reader.auth.guard'))->check();
    }

    public function rules(): array
    {
        return [
            'folder_id' => ['nullable', 'integer', 'exists:bookmark_folders,id'],
        ];
    }
}
