<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotifApiService
{
    public function sendMessage(string $phone, string $message): array
    {
        $response = Http::timeout(60)
            ->acceptJson()
            ->asJson()
            ->post(config('services.notifapi.url'), [
                'phone_no'    => $phone,
                'key'         => config('services.notifapi.key'),
                'message'     => $message,
                'skip_link'   => true,
                'flag_retry'  => 'on',
                'pendingTime' => 3,
            ]);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }
}