<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CsvResponse
{
    /**
     * @param  array<int, string>  $header
     * @param  iterable<array<int, mixed>>  $rows
     */
    public static function download(string $filename, array $header, iterable $rows): StreamedResponse
    {
        $name = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: 'export';

        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // UTF-8 BOM so Excel reads Urdu / special characters correctly

            fputcsv($out, array_map([self::class, 'cell'], $header));

            foreach ($rows as $row) {
                fputcsv($out, array_map([self::class, 'cell'], $row));
            }

            fclose($out);
        }, $name . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Text that starts with = + - @ would run as a formula in Excel; numbers are left alone. */
    public static function cell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if ($value !== '' && ! is_numeric($value) && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }
}
