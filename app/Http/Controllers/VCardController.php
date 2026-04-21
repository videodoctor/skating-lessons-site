<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class VCardController extends Controller
{
    public function show(): Response
    {
        $vcard = "BEGIN:VCARD\r\n"
            . "VERSION:3.0\r\n"
            . "FN:Kristine Skates\r\n"
            . "N:Humphrey;Kristine;;;\r\n"
            . "ORG:Kristine Skates\r\n"
            . "TITLE:Skating Coach\r\n"
            . "TEL;TYPE=CELL,VOICE:+13143147528\r\n"
            . "EMAIL;TYPE=WORK:kristine@kristineskates.com\r\n"
            . "URL:https://kristineskates.com\r\n"
            . "END:VCARD\r\n";

        return response($vcard, 200, [
            'Content-Type' => 'text/vcard; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kristine-skates.vcf"',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
