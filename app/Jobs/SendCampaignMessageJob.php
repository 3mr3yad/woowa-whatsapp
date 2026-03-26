<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\MessageLog;
use App\Services\NotifApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class SendCampaignMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $campaignId,
        private int $contactId,
    ) {
    }

    public function handle(NotifApiService $notifApiService): void
    {
        $campaign = Campaign::find($this->campaignId);
        $contact = Contact::find($this->contactId);

        if (!$campaign || !$contact) {
            return;
        }

        if ($contact->done_send) {
            return;
        }

        $template = $contact->message ?: $campaign->message;
        $name = (string) ($contact->name ?? '');
        $finalMessage = str_replace(['{name}', '{{name}}'], $name, (string) $template);

        $fileUrl = $contact->file_url;

        $imageUrl = null;
        if ($contact->image_url) {
            $imageUrl = $contact->image_url;
        }
        if ($campaign->image_path) {
            $publicPath = Storage::disk('public')->url($campaign->image_path);
            $imageUrl = $imageUrl ?: (rtrim((string) config('app.url'), '/') . $publicPath);
        }

        try {
            $result = $imageUrl
                ? $notifApiService->sendImageUrl($contact->phone, $imageUrl, $finalMessage)
                : $notifApiService->sendMessage($contact->phone, $finalMessage);

            MessageLog::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'phone' => $contact->phone,
                'name' => $contact->name,
                'message' => $imageUrl ? ('[IMAGE] ' . $imageUrl . "\n" . $finalMessage) : $finalMessage,
                'api_response' => $result['body'],
                'http_code' => $result['status'],
                'status' => $result['success'] ? 'success' : 'failed',
                'error_message' => $result['success'] ? null : 'API request failed',
                'sent_at' => now(),
            ]);

            if ($result['success']) {
                $contact->update(['done_send' => true]);
            }
        } catch (\Throwable $e) {
            MessageLog::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'phone' => $contact->phone,
                'name' => $contact->name,
                'message' => $finalMessage,
                'api_response' => null,
                'http_code' => null,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }

        if ($fileUrl) {
            try {
                $fileResult = $notifApiService->sendFileUrl($contact->phone, $fileUrl);

                MessageLog::create([
                    'campaign_id' => $campaign->id,
                    'contact_id' => $contact->id,
                    'phone' => $contact->phone,
                    'name' => $contact->name,
                    'message' => '[FILE] ' . $fileUrl,
                    'api_response' => $fileResult['body'],
                    'http_code' => $fileResult['status'],
                    'status' => $fileResult['success'] ? 'success' : 'failed',
                    'error_message' => $fileResult['success'] ? null : 'API request failed',
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                MessageLog::create([
                    'campaign_id' => $campaign->id,
                    'contact_id' => $contact->id,
                    'phone' => $contact->phone,
                    'name' => $contact->name,
                    'message' => '[FILE] ' . $fileUrl,
                    'api_response' => null,
                    'http_code' => null,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'sent_at' => now(),
                ]);
            }
        }

        $total = $campaign->contacts()->count();
        $processed = (int) $campaign->logs()->whereNotNull('contact_id')->distinct('contact_id')->count('contact_id');

        if ($total > 0 && $processed >= $total) {
            $hasFailed = $campaign->logs()->where('status', 'failed')->exists();
            $campaign->update(['status' => $hasFailed ? 'failed' : 'completed']);
        }
    }
}
