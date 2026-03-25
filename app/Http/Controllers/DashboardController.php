<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $userId = Auth::id();

        $campaignsQuery = Campaign::query()->where('created_by', $userId);

        $campaignCounts = [
            'total' => (clone $campaignsQuery)->count(),
            'draft' => (clone $campaignsQuery)->where('status', 'draft')->count(),
            'processing' => (clone $campaignsQuery)->where('status', 'processing')->count(),
            'completed' => (clone $campaignsQuery)->where('status', 'completed')->count(),
            'failed' => (clone $campaignsQuery)->where('status', 'failed')->count(),
        ];

        $campaignIds = (clone $campaignsQuery)->pluck('id');

        $logsQuery = MessageLog::query()->whereIn('campaign_id', $campaignIds);
        $logCounts = [
            'total' => (clone $logsQuery)->count(),
            'success' => (clone $logsQuery)->where('status', 'success')->count(),
            'failed' => (clone $logsQuery)->where('status', 'failed')->count(),
        ];

        $contactsQuery = Contact::query()->whereIn('campaign_id', $campaignIds);
        $contactCounts = [
            'total' => (clone $contactsQuery)->count(),
            'sent' => (clone $contactsQuery)->where('done_send', true)->count(),
            'pending' => (clone $contactsQuery)->where('done_send', false)->count(),
        ];

        $recentCampaigns = (clone $campaignsQuery)
            ->latest()
            ->withCount([
                'contacts',
                'contacts as sent_contacts_count' => fn ($q) => $q->where('done_send', true),
                'logs',
                'logs as success_logs_count' => fn ($q) => $q->where('status', 'success'),
                'logs as failed_logs_count' => fn ($q) => $q->where('status', 'failed'),
            ])
            ->take(5)
            ->get();

        $system = [
            'notifapi_url' => (string) config('services.notifapi.url'),
            'notifapi_key_set' => (string) config('services.notifapi.key') !== '',
            'queue_connection' => (string) config('queue.default'),
        ];

        return view('dashboard', compact('campaignCounts', 'logCounts', 'contactCounts', 'recentCampaigns', 'system'));
    }
}
