<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    public function index()
    {
        $templates = NotificationTemplate::orderBy('channel')->orderBy('category')->orderBy('label')->get();
        $smsTemplates = $templates->where('channel', 'sms');
        $emailTemplates = $templates->where('channel', 'email');
        $categories = Client::NOTIFICATION_CATEGORIES;

        return view('admin.notifications', compact('smsTemplates', 'emailTemplates', 'categories'));
    }

    public function update(Request $request, NotificationTemplate $template)
    {
        $request->validate([
            'subject' => 'nullable|string|max:500',
            'body'    => 'required|string|max:5000',
        ]);

        $template->update([
            'subject' => $request->subject,
            'body'    => $request->body,
        ]);

        NotificationTemplate::clearCache($template->key);

        return back()->with('success', "Template \"{$template->label}\" updated.");
    }

    public function toggle(NotificationTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        NotificationTemplate::clearCache($template->key);

        return back()->with('success', $template->is_active ? "Enabled: {$template->label}" : "Disabled: {$template->label}");
    }

    public function preview(Request $request, NotificationTemplate $template)
    {
        $sampleVars = [
            'client_name'       => 'Jane Smith',
            'first_name'        => 'Jane',
            'student_name'      => 'Mick Murray',
            'service_name'      => 'Private Lesson (30 min)',
            'lesson_date'       => 'Saturday, April 12, 2026',
            'lesson_time'       => '3:30 PM',
            'rink_name'         => 'Creve Coeur Ice Arena',
            'rink_address'      => '11400 Olde Cabin Rd, Creve Coeur, MO',
            'price'             => '55.00',
            'confirmation_code' => 'ABC12345',
            'venmo_link'        => 'venmo://paycharge?txn=pay&recipients=Kristine-Humphrey&amount=55.00',
            'code'              => '483291',
            'skate_times'       => 'Creve Coeur 9:15AM-1:00PM, Brentwood 2:00PM-4:00PM',
            'lesson_list'       => 'Sat Apr 12 3:30PM Creve Coeur, Wed Apr 16 4:00PM Brentwood',
        ];

        $rendered = NotificationTemplate::substitute($template->body, $sampleVars);
        $subject = $template->subject ? NotificationTemplate::substitute($template->subject, $sampleVars) : null;

        return response()->json([
            'subject'  => $subject,
            'body'     => $rendered,
            'channel'  => $template->channel,
        ]);
    }
}
