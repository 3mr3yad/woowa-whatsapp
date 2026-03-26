<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotifApiService
{
    private function normalizeResult($response): array
    {
        $body = (string) $response->body();

        $decoded = null;
        try {
            $decoded = $response->json();
        } catch (\Throwable $e) {
            $decoded = null;
        }

        $success = $response->successful();
        if (is_array($decoded)) {
            if (array_key_exists('success', $decoded)) {
                $success = (bool) $decoded['success'];
            } elseif (array_key_exists('ok', $decoded)) {
                $success = (bool) $decoded['ok'];
            } elseif (array_key_exists('status', $decoded)) {
                $status = $decoded['status'];
                if (is_bool($status)) {
                    $success = $status;
                } elseif (is_string($status)) {
                    $success = in_array(strtolower($status), ['success', 'ok', 'sent'], true);
                } elseif (is_int($status)) {
                    $success = $status === 1;
                }
            }
        }

        return [
            'success' => $success,
            'status' => $response->status(),
            'body' => $body,
        ];
    }

    private function baseUrl(): string
    {
        $url = rtrim((string) config('services.notifapi.url'), '/');
        $known = [
            '/send_message',
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
        $url = $this->baseUrl() . '/send_message';

        $response = Http::timeout(60)
            ->acceptJson()
            ->asJson()
            ->post($url, [
                'phone_no' => $phone,
                'key' => config('services.notifapi.key'),
                'message' => $message,
                'skip_link' => true,
                'flag_retry' => 'on',
                'pendingTime' => 3,
            ]);

        return $this->normalizeResult($response);
    }

    public function sendMessageAsync(string $phone, string $message): array
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

        return $this->normalizeResult($response);
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

        return $this->normalizeResult($response);
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

        return $this->normalizeResult($response);
    }
}