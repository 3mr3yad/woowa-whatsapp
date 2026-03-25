<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotifApiService
{
    private function baseUrl(): string
    {
        $url = rtrim((string) config('services.notifapi.url'), '/');
        $known = [
            '/async_send_message',
            '/async_send_file_url',
            '/send_image_url',
            '/send_file_url',
        ];

        foreach ($known as $suffix) {
            if (str_ends_with($url, $suffix)) {
                $url = substr($url, 0, -strlen($suffix));
                $url = rtrim($url, '/');
                break;
            }
        }

        return $url;
    }

    public function sendMessage(string $phone, string $message): array
    {
        $url = $this->baseUrl() . '/async_send_message';

        $response = Http::timeout(60)
            ->acceptJson()
            ->asJson()
            ->post($url, [
                'phone_no' => $phone,
                'key' => config('services.notifapi.key'),
                'message' => $message,
            ]);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }

    public function sendImageUrl(string $phone, string $imageUrl, string $message = ''): array
    {
        $url = $this->baseUrl() . '/send_image_url';

        $response = Http::timeout(60)
            ->acceptJson()
            ->asJson()
            ->post($url, [
                'phone_no' => $phone,
                'key' => config('services.notifapi.key'),
                'url' => $imageUrl,
                'message' => $message,
            ]);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }

    public function sendFileUrl(string $phone, string $fileUrl): array
    {
        $url = $this->baseUrl() . '/async_send_file_url';

        $response = Http::timeout(60)
            ->acceptJson()
            ->asJson()
            ->post($url, [
                'phone_no' => $phone,
                'key' => config('services.notifapi.key'),
                'url' => $fileUrl,
            ]);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }
}