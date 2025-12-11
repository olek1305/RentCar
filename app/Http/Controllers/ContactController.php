<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendContactRequest;
use App\Mail\ContactFormMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(SendContactRequest $request): RedirectResponse
    {
        #TODO what this is file?
        $validated = $request->validated();

        Mail::to('contact@carshop.pl')->send(new ContactFormMail($validated));

        return back()->with('success', 'Your message has been sent!');
    }
}
