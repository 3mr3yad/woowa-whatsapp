<?php

namespace App\Http\Controllers;

use App\Imports\ContactsImport;
use App\Jobs\SendCampaignMessageJob;
use App\Models\Campaign;
use App\Models\MessageLog;
use App\Services\NotifApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::latest()->paginate(10);
        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('campaigns.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        DB::beginTransaction();

        try {
            $path = $request->file('file')->store('campaigns', 'public');

            $campaign = Campaign::create([
                'title' => $request->title,
                'message' => $request->message,
                'excel_file' => $path,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            Excel::import(new ContactsImport($campaign->id), $request->file('file'));

            DB::commit();

            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('success', 'Campaign created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['contacts', 'logs' => fn ($q) => $q->latest()]);
        return view('campaigns.show', compact('campaign'));
    }

    public function send(Campaign $campaign, NotifApiService $notifApiService)
    {
        $campaign->update(['status' => 'processing']);

        $delaySeconds = (int) config('services.notifapi.delay_seconds', 1);
        if ($delaySeconds < 0) {
            $delaySeconds = 0;
        }
        $i = 0;
        foreach ($campaign->contacts()->where('done_send', false)->get() as $contact) {
            $job = new SendCampaignMessageJob($campaign->id, $contact->id);
            $job->delay(now()->addSeconds($i * $delaySeconds));
            dispatch($job);
            $i++;
        }

        if (request()->expectsJson()) {
            return Response::json(['ok' => true]);
        }

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', 'Send started.');
    }

    public function progress(Campaign $campaign)
    {
        $total = $campaign->contacts()->count();
        $done = (int) $campaign->logs()->whereNotNull('contact_id')->distinct('contact_id')->count('contact_id');

        $percent = $total > 0 ? (int) floor(min(100, ($done / $total) * 100)) : 0;

        return Response::json([
            'status' => $campaign->status,
            'done' => $done,
            'total' => $total,
            'percent' => $percent,
        ]);
    }
}