<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\MessageLog;
use App\Services\NotifApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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

        try {
            $result = $notifApiService->sendMessage($contact->phone, $campaign->message);

            MessageLog::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'phone' => $contact->phone,
                'name' => $contact->name,
                'message' => $campaign->message,
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
                'message' => $campaign->message,
                'api_response' => null,
                'http_code' => null,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }

        $total = $campaign->contacts()->count();
        $processed = (int) $campaign->logs()->whereNotNull('contact_id')->distinct('contact_id')->count('contact_id');

        if ($total > 0 && $processed >= $total) {
            $hasFailed = $campaign->logs()->where('status', 'failed')->exists();
            $campaign->update(['status' => $hasFailed ? 'failed' : 'completed']);
        }
    }
}
