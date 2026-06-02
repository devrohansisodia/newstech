<?php

namespace NewsTech\Reader\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use NewsTech\Reader\Http\Requests\StoreReaderRegistrationRequest;
use NewsTech\Reader\Repositories\ReaderRepository;

class RegisteredReaderController extends Controller
{
    public function __construct(protected ReaderRepository $readers) {}

    public function create(): View
    {
        return view('newstech-frontend::readers.register');
    }

    public function store(StoreReaderRegistrationRequest $request): RedirectResponse
    {
        $reader = $this->readers->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'is_active' => true,
        ]);

        Auth::guard(config('newstech-reader.auth.guard'))->login($reader);
        $request->session()->regenerate();
        $reader->sendEmailVerificationNotification();

        return redirect()->route(config('newstech-reader.auth.redirect_to'));
    }
}
