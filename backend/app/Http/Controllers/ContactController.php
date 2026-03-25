<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        Mail::send('emails.contact', [
            'contactName' => $request->name,
            'contactEmail' => $request->email,
            'contactSubject' => $request->subject,
            'contactMessage' => $request->message,
        ], function ($msg) use ($request) {
            $msg->to(config('mail.from.address'))
                ->replyTo($request->email, $request->name)
                ->subject('Contacto web: ' . $request->subject);
        });

        return response()->json(['message' => 'Mensaje enviado correctamente. Te responderemos a la brevedad.']);
    }
}
