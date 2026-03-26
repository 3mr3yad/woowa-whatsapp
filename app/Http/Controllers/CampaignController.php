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
use Illuminate\Support\Facades\Storage;
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
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        DB::beginTransaction();

        try {
            $path = $request->file('file')->store('campaigns', 'public');

            $imagePath = null;
            $campaignImageUrl = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('campaign-images', 'public');
                $publicUrl = (string) Storage::disk('public')->url($imagePath);
                if (str_starts_with($publicUrl, 'http://') || str_starts_with($publicUrl, 'https://')) {
                    $campaignImageUrl = $publicUrl;
                } else {
                    $base = rtrim((string) $request->getSchemeAndHttpHost(), '/');
                    $campaignImageUrl = $base . '/' . ltrim($publicUrl, '/');
                }
            }

            $campaign = Campaign::create([
                'title' => $request->title,
                'message' => $request->message,
                'excel_file' => $path,
                'image_path' => $imagePath,
                'campaign_image_url' => $campaignImageUrl,
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

    public function edit(Campaign $campaign)
    {
        return view('campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'title' => $request->title,
                'message' => $request->message,
            ];

            if ($request->hasFile('image')) {
                if ($campaign->image_path) {
                    Storage::disk('public')->delete($campaign->image_path);
                }
                $data['image_path'] = $request->file('image')->store('campaign-images', 'public');

                $publicUrl = (string) Storage::disk('public')->url($data['image_path']);
                if (str_starts_with($publicUrl, 'http://') || str_starts_with($publicUrl, 'https://')) {
                    $data['campaign_image_url'] = $publicUrl;
                } else {
                    $base = rtrim((string) $request->getSchemeAndHttpHost(), '/');
                    $data['campaign_image_url'] = $base . '/' . ltrim($publicUrl, '/');
                }
            } elseif ($request->boolean('remove_image')) {
                if ($campaign->image_path) {
                    Storage::disk('public')->delete($campaign->image_path);
                }
                $data['image_path'] = null;
                $data['campaign_image_url'] = null;
            }

            $campaign->update($data);

            DB::commit();

            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('success', 'Campaign updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Campaign $campaign)
    {
        DB::beginTransaction();

        try {
            if ($campaign->excel_file) {
                Storage::disk('public')->delete($campaign->excel_file);
            }

            if ($campaign->image_path) {
                Storage::disk('public')->delete($campaign->image_path);
            }

            $campaign->logs()->delete();
            $campaign->contacts()->delete();
            $campaign->delete();

            DB::commit();

            return redirect()
                ->route('campaigns.index')
                ->with('success', 'Campaign deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
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