<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $emails = (string) env('LOG_VIEWER_EMAILS', '');
        $allowedEmails = array_values(array_filter(array_map('trim', explode(',', $emails))));

        $user = $request->user();
        if (!empty($allowedEmails) && (!$user || !in_array((string) $user->email, $allowedEmails, true))) {
            abort(403);
        }

        $lines = (int) $request->query('lines', 300);
        if ($lines < 50) {
            $lines = 50;
        }
        if ($lines > 2000) {
            $lines = 2000;
        }

        $path = storage_path('logs/laravel.log');
        $content = '';
        if (is_file($path)) {
            $content = $this->tailFile($path, $lines);
        }

        return view('system.logs', [
            'content' => $content,
            'lines' => $lines,
            'path' => $path,
        ]);
    }

    private function tailFile(string $path, int $lines): string
    {
        $fp = fopen($path, 'rb');
        if ($fp === false) {
            return '';
        }

        $buffer = '';
        $chunkSize = 8192;
        $pos = -1;
        $lineCount = 0;

        fseek($fp, 0, SEEK_END);
        $fileSize = ftell($fp);

        while ($fileSize + $pos >= 0 && $lineCount <= $lines) {
            $seek = max($fileSize + $pos - $chunkSize + 1, 0);
            $read = (int) (($fileSize + $pos) - $seek + 1);
            fseek($fp, $seek);
            $chunk = fread($fp, $read);
            if ($chunk === false) {
                break;
            }

            $buffer = $chunk . $buffer;
            $lineCount = substr_count($buffer, "\n");

            $pos -= $chunkSize;
            if ($seek === 0) {
                break;
            }
        }

        fclose($fp);

        $allLines = preg_split("/\r\n|\r|\n/", $buffer);
        if ($allLines === false) {
            return $buffer;
        }

        $tail = array_slice($allLines, -$lines);
        return implode("\n", $tail);
    }
}
