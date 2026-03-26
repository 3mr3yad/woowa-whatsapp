<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixCampaignImageUrls extends Command
{
    protected $signature = 'campaigns:fix-image-urls';
    protected $description = 'Fix campaign image URLs that contain localhost';

    public function handle()
    {
        $this->info('Fixing campaign image URLs...');
        
        $campaigns = Campaign::whereNotNull('campaign_image_url')
            ->where('campaign_image_url', 'like', '%localhost%')
            ->get();
            
        $fixed = 0;
        
        foreach ($campaigns as $campaign) {
            if ($campaign->image_path) {
                $publicUrl = (string) Storage::disk('public')->url($campaign->image_path);
                
                if (str_starts_with($publicUrl, 'http://') || str_starts_with($publicUrl, 'https://')) {
                    $newUrl = $publicUrl;
                } else {
                    $newUrl = rtrim(config('app.url'), '/') . '/storage/' . $campaign->image_path;
                }
                
                $campaign->update(['campaign_image_url' => $newUrl]);
                $fixed++;
                
                $this->line("Fixed Campaign ID: {$campaign->id} - New URL: {$newUrl}");
            }
        }
        
        $this->info("Fixed {$fixed} campaign image URLs.");
        
        return Command::SUCCESS;
    }
}
