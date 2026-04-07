<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;

class CheckA2PCampaign extends Command
{
    protected $signature = 'twilio:check-campaign';
    protected $description = 'Check Twilio A2P 10DLC campaign status and log changes';

    public function handle()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');

        if (!$sid || !$token) {
            $this->warn('Twilio credentials not configured.');
            return;
        }

        try {
            $client = new TwilioClient($sid, $token);

            // Check brand
            $brands = $client->messaging->v1->brandRegistrations->read(5);
            foreach ($brands as $brand) {
                $this->info("Brand: {$brand->sid} — Status: {$brand->status}");
                if ($brand->failureReason) {
                    $this->error("  Failure: {$brand->failureReason}");
                }
            }

            // Check campaigns
            $services = $client->messaging->v1->services->read(5);
            foreach ($services as $service) {
                $campaigns = $client->messaging->v1
                    ->services($service->sid)
                    ->usAppToPerson
                    ->read(10);

                foreach ($campaigns as $campaign) {
                    $status = $campaign->campaignStatus;
                    $cacheKey = "a2p_campaign_status_{$campaign->sid}";
                    $previousStatus = Cache::get($cacheKey);

                    $this->info("Campaign: {$campaign->sid}");
                    $this->info("  Status: {$status}");
                    $this->info("  Campaign ID: {$campaign->campaignId}");

                    if ($previousStatus && $previousStatus !== $status) {
                        $msg = "A2P Campaign status changed: {$previousStatus} → {$status}";
                        $this->warn($msg);
                        Log::warning($msg, [
                            'campaign_sid' => $campaign->sid,
                            'campaign_id'  => $campaign->campaignId,
                            'old_status'   => $previousStatus,
                            'new_status'   => $status,
                        ]);

                        \App\Models\SiteSetting::set('a2p_campaign_status', $status);
                        \App\Models\SiteSetting::set('a2p_campaign_last_checked', now()->toIso8601String());
                        \App\Models\SiteSetting::set('a2p_campaign_status_changed_at', now()->toIso8601String());

                        // Send email alert on any status change
                        $this->sendStatusEmail($previousStatus, $status, $campaign->sid, $campaign->campaignId);
                    }

                    Cache::put($cacheKey, $status, now()->addDays(30));
                    \App\Models\SiteSetting::set('a2p_campaign_status', $status);
                    \App\Models\SiteSetting::set('a2p_campaign_last_checked', now()->toIso8601String());
                }
            }
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");
            Log::error("A2P campaign check failed: {$e->getMessage()}");
        }
    }

    private function sendStatusEmail(string $oldStatus, string $newStatus, string $sid, ?string $campaignId): void
    {
        $isApproved = in_array($newStatus, ['VERIFIED', 'APPROVED']);
        $isFailed = in_array($newStatus, ['FAILED', 'REJECTED']);
        $urgency = ($isApproved || $isFailed) ? 'URGENT: ' : '';
        $emoji = $isApproved ? '✅' : ($isFailed ? '❌' : '📱');

        $subject = "{$urgency}{$emoji} A2P Campaign: {$newStatus}";
        $body = "Kristine Skates A2P 10DLC Campaign Status Change\n\n"
            . "Previous: {$oldStatus}\n"
            . "New: {$newStatus}\n"
            . "Campaign SID: {$sid}\n"
            . "Campaign ID: {$campaignId}\n"
            . "Checked: " . now()->format('M j, Y g:i A T') . "\n\n";

        if ($isApproved) {
            $body .= "🎉 The campaign has been APPROVED! SMS messaging is now fully operational.\n";
        } elseif ($isFailed) {
            $body .= "⚠️ The campaign was REJECTED. Check the Twilio console for details and resubmit.\n"
                . "https://console.twilio.com/\n";
        }

        $body .= "\n— Kristine Skates Automated Monitor";

        try {
            Mail::raw($body, function ($message) use ($subject) {
                $message->to('rob@videorx.com')
                    ->from('kristine@kristineskates.com', 'Kristine Skates')
                    ->subject($subject);
            });
            $this->info("  Email alert sent to rob@videorx.com");
        } catch (\Throwable $e) {
            $this->error("  Email failed: {$e->getMessage()}");
            Log::error("A2P status email failed: {$e->getMessage()}");
        }
    }
}
