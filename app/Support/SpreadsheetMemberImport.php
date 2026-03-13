<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

class SpreadsheetMemberImport
{
    public static function read(string $path, string $extension): array
    {
        return match (strtolower($extension)) {
            'csv', 'txt' => self::readCsv($path),
            'xlsx' => self::readXlsx($path),
            default => throw new RuntimeException('Unsupported file format.'),
        };
    }

    private static function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            throw new RuntimeException('Unable to open CSV file.');
        }

        $firstLine = fgets($handle);

        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $delimiter = self::detectDelimiter($firstLine);
        rewind($handle);

        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = array_map(fn ($value) => self::normalizeCsvValue($value), $row);
        }

        fclose($handle);

        return $rows;
    }

    private static function readXlsx(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open XLSX file.');
        }

        $sharedStrings = self::sharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Worksheet not found in XLSX file.');
        }

        $sheet = simplexml_load_string($sheetXml);

        if (! $sheet || ! isset($sheet->sheetData)) {
            throw new RuntimeException('Invalid XLSX worksheet structure.');
        }

        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $attributes = $cell->attributes();
                $reference = (string) ($attributes['r'] ?? '');
                $type = (string) ($attributes['t'] ?? '');
                $index = self::columnIndexFromReference($reference);

                while (count($cells) < $index) {
                    $cells[] = '';
                }

                $cells[] = self::cellValue($cell, $type, $sharedStrings);
            }

            $rows[] = array_map(fn ($value) => trim((string) $value), $cells);
        }

        return $rows;
    }

    private static function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $shared = simplexml_load_string($xml);

        if (! $shared) {
            return [];
        }

        $strings = [];

        foreach ($shared->si as $item) {
            $text = '';

            if (isset($item->t)) {
                $text = (string) $item->t;
            } elseif (isset($item->r)) {
                foreach ($item->r as $run) {
                    $text .= (string) $run->t;
                }
            }

            $strings[] = $text;
        }

        return $strings;
    }

    private static function cellValue(\SimpleXMLElement $cell, string $type, array $sharedStrings): string
    {
        if ($type === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        $value = (string) ($cell->v ?? '');

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }

        return $value;
    }

    private static function columnIndexFromReference(string $reference): int
    {
        preg_match('/[A-Z]+/', strtoupper($reference), $matches);
        $letters = $matches[0] ?? 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max($index - 1, 0);
    }

    private static function detectDelimiter(string $line): string
    {
        $delimiters = [',' , ';', "\t", '|'];
        $winner = ',';
        $count = -1;

        foreach ($delimiters as $delimiter) {
            $currentCount = count(str_getcsv($line, $delimiter));

            if ($currentCount > $count) {
                $count = $currentCount;
                $winner = $delimiter;
            }
        }

        return $winner;
    }

    private static function normalizeCsvValue(?string $value): string
    {
        $normalized = trim((string) $value);

        return (string) preg_replace('/^\xEF\xBB\xBF/', '', $normalized);
    }
}
