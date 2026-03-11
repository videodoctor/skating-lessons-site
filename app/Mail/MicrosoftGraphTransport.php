<?php

namespace App\Mail;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Message;
use Microsoft\Graph\Model\Recipient;
use Microsoft\Graph\Model\EmailAddress;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\FileAttachment;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Email;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftGraphTransport extends AbstractTransport
{
    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private string $fromAddress;

    public function __construct(string $tenantId, string $clientId, string $clientSecret, string $fromAddress)
    {
        parent::__construct();
        $this->tenantId     = $tenantId;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->fromAddress  = $fromAddress;
    }

    protected function doSend(SentMessage $message): void
    {
        $email    = MessageConverter::toEmail($message->getOriginalMessage());
        $token    = $this->getAccessToken();
        $payload  = $this->buildPayload($email);
        $sender   = $this->fromAddress;

        $response = Http::withToken($token)
            ->post("https://graph.microsoft.com/v1.0/users/{$sender}/sendMail", $payload);

        if (!$response->successful()) {
            Log::error('Graph mail send failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Microsoft Graph mail failed: ' . $response->status() . ' — ' . $response->body());
        }
    }

    private function getAccessToken(): string
    {
        return Cache::remember('ms_graph_token', 3500, function () {
            $response = Http::asForm()->post(
                "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token",
                [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope'         => 'https://graph.microsoft.com/.default',
                ]
            );

            if (!$response->successful()) {
                throw new \RuntimeException('Failed to get Graph token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    private function buildPayload(Email $email): array
    {
        // Recipients
        $toRecipients  = $this->buildRecipients($email->getTo());
        $ccRecipients  = $this->buildRecipients($email->getCc());
        $bccRecipients = $this->buildRecipients($email->getBcc());

        // Body
        $bodyContent = $email->getHtmlBody() ?? $email->getTextBody() ?? '';
        $bodyType    = $email->getHtmlBody() ? 'HTML' : 'Text';

        $message = [
            'subject' => $email->getSubject(),
            'body'    => [
                'contentType' => $bodyType,
                'content'     => $bodyContent,
            ],
            'toRecipients' => $toRecipients,
        ];

        if (!empty($ccRecipients))  $message['ccRecipients']  = $ccRecipients;
        if (!empty($bccRecipients)) $message['bccRecipients'] = $bccRecipients;

        // From (use configured from address)
        $fromName = config('mail.from.name', 'Coach Kristine');
        $message['from'] = [
            'emailAddress' => [
                'address' => $this->fromAddress,
                'name'    => $fromName,
            ],
        ];

        // Attachments
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                '@odata.type'  => '#microsoft.graph.fileAttachment',
                'name'         => $attachment->getPreparedHeaders()->getHeaderParameter('Content-Disposition', 'filename') ?? 'attachment',
                'contentBytes' => base64_encode($attachment->getBody()),
                'contentType'  => $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
            ];
        }
        if (!empty($attachments)) {
            $message['attachments'] = $attachments;
        }

        return [
            'message'         => $message,
            'saveToSentItems' => true,
        ];
    }

    private function buildRecipients(array $addresses): array
    {
        return array_map(fn($addr) => [
            'emailAddress' => [
                'address' => $addr->getAddress(),
                'name'    => $addr->getName() ?? $addr->getAddress(),
            ],
        ], $addresses);
    }

    public function __toString(): string
    {
        return 'microsoft-graph';
    }
}
