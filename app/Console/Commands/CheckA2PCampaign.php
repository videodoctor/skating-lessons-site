<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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

                        // Log to activity for admin visibility
                        \App\Models\SiteSetting::set('a2p_campaign_status', $status);
                        \App\Models\SiteSetting::set('a2p_campaign_last_checked', now()->toIso8601String());
                        \App\Models\SiteSetting::set('a2p_campaign_status_changed_at', now()->toIso8601String());
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
}
