<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ];

        $recipient = trim(env('CONTACT_EMAIL', 'admin@petmanage.com'), " \t\n\r\0\x0B\"'");
        if ($recipient === '') {
            $recipient = 'admin@petmanage.com';
        }

        try {
            Mail::to($recipient)->send(new ContactUs($data));
            Log::info('Contact us email sent', ['recipient' => $recipient, 'from_email' => $data['email']]);
        } catch (\Throwable $e) {
            Log::error('Contact us email failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'recipient' => $recipient,
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to send message. Please try again later.',
                'result' => null,
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully.',
            'result' => null,
        ], 200);
    }
}

