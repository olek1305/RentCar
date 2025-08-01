<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendContactRequest;
use App\Mail\ContactFormMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * @param SendContactRequest $request
     * @return RedirectResponse
     */
    public function send(SendContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Mail::to('contact@carshop.pl')->send(new ContactFormMail($validated));

        return back()->with('success', 'Your message has been sent!');
    }
}
