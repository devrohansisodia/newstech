<?php

namespace NewsTech\Admin\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FormDemoController
{
    public function index(): View
    {
        return view('newstech-admin::form.demo');
    }

    public function preview(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'min:10'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:120'],
            'excerpt' => ['required', 'string', 'min:20'],
            'section' => ['required', Rule::in(['politics', 'business', 'culture'])],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.newstech.foundation.form-demo.index')
                ->withErrors($validator)
                ->withInput();
        }

        return redirect()
            ->route('admin.newstech.foundation.form-demo.index')
            ->withInput()
            ->with('form_demo_status', 'Preview only. Reusable form components accepted the current input without saving anything.');
    }
}
