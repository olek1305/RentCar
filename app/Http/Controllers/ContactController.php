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
        $validated = $request->validated();

        Mail::to('contact@carshop.pl')->send(new ContactFormMail($validated));

        return back()->with('success', __('messages.contact_message_sent'));
    }
}
