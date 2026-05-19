<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Mail\InquiryReceivedAdmin;
use App\Models\Inquiry;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class InquiryController extends Controller
{
    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        // Honeypot — checked after the field-level validation passes. Bots
        // that fill every input get a silent "success" so they don't iterate.
        if (filled($request->input('website'))) {
            return redirect()
                ->route('contact.index')
                ->with('success', "Thanks — we've received your message and will be in touch soon.");
        }

        $inquiry = Inquiry::create($request->validated());

        $adminEmail = Setting::get('admin_notification_email', 'admin@blomfree.com');
        Mail::to($adminEmail)->send(new InquiryReceivedAdmin($inquiry));

        return redirect()
            ->route('contact.index')
            ->with('success', "Thanks {$inquiry->name} — we've received your message and will reply shortly.");
    }
}
