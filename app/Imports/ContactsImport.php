<?php

namespace App\Imports;

use App\Models\Contact;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ContactsImport implements ToCollection
{
    public function __construct(private int $campaignId)
    {
    }

    public function collection(Collection $rows): void
    {
        $headerMap = null;

        foreach ($rows as $index => $row) {
            $values = $this->rowToArray($row);

            if ($index === 0) {
                $headerMap = $this->detectHeaderMap($values);
                if ($headerMap !== null) {
                    continue;
                }
            }

            $name = $this->getColumnValue($values, $headerMap, 'name', 0);
            $rawPhone = $this->getColumnValue($values, $headerMap, 'phone', 1);
            if ($rawPhone === null && $headerMap !== null) {
                $rawPhone = $this->getColumnValue($values, $headerMap, 'mobile', 1)
                    ?? $this->getColumnValue($values, $headerMap, 'msisdn', 1);
            }

            $name = $name !== null ? trim((string) $name) : null;
            $phone = $this->normalizePhone($rawPhone);
            $message = $this->getColumnValue($values, $headerMap, 'message', 2);
            $message = $message !== null ? trim((string) $message) : null;
            $fileUrl = $this->getColumnValue($values, $headerMap, 'file_url', 3);
            $fileUrl = $fileUrl !== null ? trim((string) $fileUrl) : null;
            $imageUrl = $this->getColumnValue($values, $headerMap, 'image_url', 4);
            $imageUrl = $imageUrl !== null ? trim((string) $imageUrl) : null;

            if (!$phone) {
                continue;
            }

            Contact::create([
                'campaign_id' => $this->campaignId,
                'name' => $name,
                'phone' => $phone,
                'message' => $message,
                'file_url' => $fileUrl,
                'image_url' => $imageUrl,
                'done_send' => false,
            ]);
        }
    }

    private function rowToArray(mixed $row): array
    {
        if ($row instanceof \Illuminate\Support\Collection) {
            return $row->toArray();
        }

        if (is_array($row)) {
            return $row;
        }

        if ($row instanceof \ArrayAccess) {
            $arr = [];
            foreach ($row as $k => $v) {
                $arr[$k] = $v;
            }
            return $arr;
        }

        return [];
    }

    private function detectHeaderMap(array $values): ?array
    {
        $map = [];
        foreach ($values as $i => $v) {
            if ($v === null) {
                continue;
            }

            $key = strtolower(trim((string) $v));
            $key = preg_replace('/\s+/', '', $key);

            if (in_array($key, ['name', 'fullname'], true)) {
                $map['name'] = $i;
            }

            if (in_array($key, ['phone', 'mobile', 'msisdn'], true)) {
                $map[$key] = $i;
                $map['phone'] = $map['phone'] ?? $i;
            }

            if (in_array($key, ['message', 'msg', 'text'], true)) {
                $map['message'] = $i;
            }

            if (in_array($key, ['file_url', 'url', 'file'], true)) {
                $map['file_url'] = $i;
            }

            if (in_array($key, ['image_url', 'image', 'img', 'picture'], true)) {
                $map['image_url'] = $i;
            }
        }

        return isset($map['phone']) ? $map : null;
    }

    private function getColumnValue(array $values, ?array $headerMap, string $header, int $fallbackIndex): mixed
    {
        if ($headerMap !== null && array_key_exists($header, $headerMap)) {
            $idx = $headerMap[$header];
            return $values[$idx] ?? null;
        }

        return $values[$fallbackIndex] ?? null;
    }

    private function normalizePhone(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            $digits = (string) $value;
            return $digits !== '' ? $digits : null;
        }

        if (is_float($value)) {
            $digits = number_format($value, 0, '', '');
            return $digits !== '' ? $digits : null;
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        if (preg_match('/[eE][+\-]?\d+/', $str)) {
            $asFloat = (float) $str;
            if ($asFloat > 0) {
                $str = number_format($asFloat, 0, '', '');
            }
        }

        $digits = preg_replace('/\D+/', '', $str);
        return $digits !== '' ? $digits : null;
    }
}