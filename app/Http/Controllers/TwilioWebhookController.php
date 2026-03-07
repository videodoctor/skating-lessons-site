<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TwilioWebhookController extends Controller
{
    public function inboundSms(Request $request, SmsService $sms): Response
    {
        // Twilio sends POST with From and Body params
        $from = $request->input('From', '');
        $body = $request->input('Body', '');

        if (!$from || !$body) {
            return response('', 400);
        }

        $replyMessage = $sms->handleReply($from, $body);

        // Respond with TwiML to send reply SMS
        $twiml = '<?xml version="1.0" encoding="UTF-8"?>'
               . '<Response><Message>' . htmlspecialchars($replyMessage) . '</Message></Response>';

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }
}
