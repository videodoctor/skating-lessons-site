<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Client;
use App\Models\VenmoPayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ParseVenmoEmails extends Command
{
    protected $signature   = 'venmo:parse-emails {--dry-run : Show what would be matched without saving}';
    protected $description = 'Parse Venmo payment emails from venmo@kristineskates.com and match to bookings';

    private string $mailbox = 'venmo@kristineskates.com';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $this->info($isDryRun ? '🔍 DRY RUN — no changes will be saved' : '📬 Parsing Venmo emails...');

        $token    = $this->getAccessToken();
        $messages = $this->fetchUnreadVenmoEmails($token);

        if (empty($messages)) {
            $this->info('No Venmo payment emails found.');
            return 0;
        }

        $this->info('Found ' . count($messages) . ' Venmo email(s) to process.');

        foreach ($messages as $message) {
            $this->processMessage($message, $token, $isDryRun);
        }

        return 0;
    }

    private function fetchUnreadVenmoEmails(string $token): array
    {
        // Get all emails from venmo@venmo.com (not just unread — dedup by transaction_id)
        $response = Http::withToken($token)
            ->get("https://graph.microsoft.com/v1.0/users/{$this->mailbox}/messages", [
                '$select'  => 'id,subject,body,receivedDateTime,isRead,from',
                '$filter'  => "from/emailAddress/address eq 'venmo@venmo.com'",
                '$top'     => 50,
                '$orderby' => 'receivedDateTime desc',
            ]);

        if (!$response->successful()) {
            Log::error('Venmo mailbox read failed', ['status' => $response->status(), 'body' => $response->body()]);
            $this->error('Failed to read mailbox: ' . $response->status());
            return [];
        }

        return $response->json('value', []);
    }

    private function processMessage(array $message, string $token, bool $isDryRun): void
    {
        $subject = $message['subject'] ?? '';
        $body    = strip_tags($message['body']['content'] ?? '');
        $msgId   = $message['id'];

        // Only process "paid you" emails
        if (!str_contains(strtolower($subject), 'paid you')) {
            $this->line("  ↷ Skipping: {$subject}");
            if (!$isDryRun) $this->markAsRead($token, $msgId);
            return;
        }

        $parsed = $this->parseEmail($subject, $body, $message['receivedDateTime']);

        if (!$parsed) {
            $this->warn("  ✕ Could not parse: {$subject}");
            return;
        }

        $this->line("  📧 {$parsed['sender_name']} paid \${$parsed['amount']} — note: \"{$parsed['note']}\"");

        // Check for duplicate by transaction ID
        if (VenmoPayment::where('transaction_id', $parsed['transaction_id'])->exists()) {
            $this->line("     ↷ Already recorded (transaction ID match)");
            if (!$isDryRun) $this->markAsRead($token, $msgId);
            return;
        }

        // Try to match to a booking
        $booking = $this->matchToBooking($parsed);
        $client  = $this->matchToClient($parsed);

        if ($booking) {
            $this->info("     ✓ Matched to booking #{$booking->id} for " . ($booking->student?->first_name ?? $booking->client_name));
        } elseif ($client) {
            $this->info("     ~ Matched to client: {$client->full_name} (no specific booking matched)");
        } else {
            $this->warn("     ⚠ No booking or client match found for \"{$parsed['sender_name']}\"");
        }

        if (!$isDryRun) {
            // Save VenmoPayment record
            VenmoPayment::create([
                'transaction_id'  => $parsed['transaction_id'],
                'sender_name'     => $parsed['sender_name'],
                'amount'          => $parsed['amount'],
                'note'            => $parsed['note'],
                'paid_at'         => $parsed['paid_at'],
                'booking_id'      => $booking?->id,
                'client_id'       => $client?->id ?? $booking?->client_id,
                'match_status'    => $booking ? 'matched' : ($client ? 'client_only' : 'unmatched'),
                'raw_subject'     => $subject,
            ]);

            // Auto-mark booking as venmo paid if matched
            if ($booking && $booking->payment_status !== 'paid') {
                $booking->update([
                    'payment_type'        => 'venmo',
                    'payment_status'      => 'paid',
                    'venmo_confirmed_at'  => now(),
                ]);
                $this->info("     💜 Booking #{$booking->id} marked as Venmo paid!");
            }

            $this->markAsRead($token, $msgId);
        }
    }

    private function parseEmail(string $subject, string $body, string $receivedAt): ?array
    {
        // Parse subject: "Jeff Stevens paid you $400.00"
        if (!preg_match('/^(.+?)\s+paid you\s+\$?([\d,]+\.?\d*)/i', $subject, $subjectMatch)) {
            return null;
        }

        $senderName = trim($subjectMatch[1]);
        $amount     = (float) str_replace(',', '', $subjectMatch[2]);

        // Parse transaction ID from body
        $transactionId = '';
        if (preg_match('/Transaction ID[\s\n]+(\d+)/i', $body, $txMatch)) {
            $transactionId = trim($txMatch[1]);
        }

        // Parse note — text between sender line and "See transaction" or "Money credited"
        $note = '';
        // Try to find note after the amount block
        if (preg_match('/\d{2}\s*\n+([^\n]+)\n+(?:See transaction|Money credited)/i', $body, $noteMatch)) {
            $note = trim($noteMatch[1]);
        } elseif (preg_match('/paid you\s*\$[\d,.]+\s*\n+([^\n]+)\n/i', $body, $noteMatch2)) {
            $note = trim($noteMatch2[1]);
        }

        // Parse date
        $paidAt = Carbon::parse($receivedAt);

        return [
            'sender_name'    => $senderName,
            'amount'         => $amount,
            'note'           => $note,
            'transaction_id' => $transactionId,
            'paid_at'        => $paidAt,
        ];
    }

    private function matchToBooking(array $parsed): ?Booking
    {
        // 1. Try to match by confirmation code in note
        if ($parsed['note']) {
            $booking = Booking::where('confirmation_code', strtoupper(trim($parsed['note'])))
                ->whereIn('status', ['confirmed', 'pending'])
                ->first();
            if ($booking) return $booking;

            // Also try partial match — note might contain code among other text
            preg_match_all('/\b[A-Z0-9]{6,8}\b/', strtoupper($parsed['note']), $codes);
            foreach ($codes[0] ?? [] as $code) {
                $booking = Booking::where('confirmation_code', $code)->first();
                if ($booking) return $booking;
            }
        }

        // 2. Try to match by amount + client name + recent date
        $client = $this->matchToClient($parsed);
        if ($client) {
            $booking = Booking::where('client_id', $client->id)
                ->where('price_paid', $parsed['amount'])
                ->whereIn('status', ['confirmed', 'pending'])
                ->where('payment_status', '!=', 'paid')
                ->where('date', '>=', now()->subDays(7)->toDateString())
                ->orderByDesc('date')
                ->first();
            if ($booking) return $booking;
        }

        return null;
    }

    private function matchToClient(array $parsed): ?Client
    {
        $nameParts = explode(' ', $parsed['sender_name']);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        // Exact full name match
        $client = Client::where('name', $parsed['sender_name'])
            ->orWhere(fn($q) => $q->where('first_name', $firstName)->where('last_name', $lastName))
            ->first();

        if ($client) return $client;

        // First name only match (common for parents with different last names)
        if ($firstName) {
            return Client::where('first_name', $firstName)->first();
        }

        return null;
    }

    private function markAsRead(string $token, string $messageId): void
    {
        Http::withToken($token)
            ->patch("https://graph.microsoft.com/v1.0/users/{$this->mailbox}/messages/{$messageId}", [
                'isRead' => true,
            ]);
    }

    private function getAccessToken(): string
    {
        return Cache::remember('ms_graph_token', 3500, function () {
            $tenantId     = config('services.microsoft_graph.tenant_id');
            $clientId     = config('services.microsoft_graph.client_id');
            $clientSecret = config('services.microsoft_graph.client_secret');

            $response = Http::asForm()->post(
                "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
                [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'scope'         => 'https://graph.microsoft.com/.default',
                ]
            );

            if (!$response->successful()) {
                throw new \RuntimeException('Failed to get Graph token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }
}
