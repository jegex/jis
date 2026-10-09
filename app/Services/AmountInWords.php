<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\App;

final class AmountInWords
{
    /** @var array<int, string> */
    private const ENGLISH_ONES = [
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
    ];

    /** @var array<int, string> */
    private const ENGLISH_TENS = [
        2 => 'Twenty',
        3 => 'Thirty',
        4 => 'Forty',
        5 => 'Fifty',
        6 => 'Sixty',
        7 => 'Seventy',
        8 => 'Eighty',
        9 => 'Ninety',
    ];

    /** @var array<int, string> */
    private const ENGLISH_SCALES = ['', ' Thousand', ' Million', ' Billion', ' Trillion'];

    /** @var array<int, string> */
    private const INDONESIAN_ONES = [
        0 => '',
        1 => 'satu',
        2 => 'dua',
        3 => 'tiga',
        4 => 'empat',
        5 => 'lima',
        6 => 'enam',
        7 => 'tujuh',
        8 => 'delapan',
        9 => 'sembilan',
    ];

    /** @var array<int, string> */
    private const INDONESIAN_SCALES = ['', 'ribu', 'juta', 'miliar', 'triliun'];

    public function toWords(float $amount, ?string $currencyCode = null, ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $currency = $this->currency($currencyCode);

        $code = $currency?->code ?? $currencyCode ?? 'IDR';
        $name = $currency?->name ?? $code;
        $decimalPlaces = (int) ($currency?->decimal_place ?? 0);

        $scale = (int) (10 ** $decimalPlaces);
        $value = (int) round(abs($amount) * $scale);
        $major = intdiv($value, $scale);
        $fraction = $value % $scale;

        $majorWords = $this->numberToWords($major, $locale);

        if ($decimalPlaces > 0 && $fraction > 0) {
            $fractionWords = $this->numberToWords($fraction, $locale);
            $minorUnit = $this->isIndonesian($locale) ? 'Sen' : 'Cents';
            $connector = $this->isIndonesian($locale) ? 'dan' : 'and';

            return "{$majorWords} {$name} {$connector} {$fractionWords} {$minorUnit} ({$code})";
        }

        return "{$majorWords} {$name} ({$code})";
    }

    private function currency(?string $currencyCode): ?Currency
    {
        $code = $currencyCode ?? Currency::getDefault()?->code;

        if ($code === null) {
            return null;
        }

        return Currency::query()->where('code', $code)->first();
    }

    private function isIndonesian(string $locale): bool
    {
        return str_starts_with(mb_strtolower($locale), 'id');
    }

    private function numberToWords(int $number, string $locale): string
    {
        $isIndonesian = $this->isIndonesian($locale);

        $words = $isIndonesian
            ? $this->indonesianWords($number)
            : $this->englishWords($number);

        return $isIndonesian ? ucfirst($words) : $words;
    }

    private function englishWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $chunks = [];
        $scale = 0;

        while ($number > 0) {
            $chunk = $number % 1000;

            if ($chunk > 0) {
                $chunks[] = $this->englishChunk($chunk).self::ENGLISH_SCALES[$scale];
            }

            $number = intdiv($number, 1000);
            $scale++;
        }

        return implode(' ', array_reverse($chunks));
    }

    private function englishChunk(int $number): string
    {
        $words = [];

        if ($number >= 100) {
            $words[] = self::ENGLISH_ONES[intdiv($number, 100)].' Hundred';
            $number %= 100;
        }

        if ($number >= 20) {
            $tens = self::ENGLISH_TENS[intdiv($number, 10)];
            $number %= 10;
            $words[] = $number > 0 ? "{$tens}-".self::ENGLISH_ONES[$number] : $tens;
            $number = 0;
        }

        if ($number > 0) {
            $words[] = self::ENGLISH_ONES[$number];
        }

        return implode(' ', $words);
    }

    private function indonesianWords(int $number): string
    {
        if ($number === 0) {
            return 'nol';
        }

        $chunks = [];
        $scale = 0;

        while ($number > 0) {
            $chunk = $number % 1000;

            if ($chunk > 0) {
                $chunks[] = $this->indonesianChunk($chunk, $scale);
            }

            $number = intdiv($number, 1000);
            $scale++;
        }

        return implode(' ', array_reverse($chunks));
    }

    private function indonesianChunk(int $number, int $scale): string
    {
        if ($scale === 1 && $number === 1) {
            return 'seribu';
        }

        $words = [];

        if ($number >= 100) {
            $hundreds = intdiv($number, 100);
            $words[] = $hundreds === 1 ? 'seratus' : self::INDONESIAN_ONES[$hundreds].' ratus';
            $number %= 100;
        }

        if ($number === 10) {
            $words[] = 'sepuluh';
            $number = 0;
        } elseif ($number === 11) {
            $words[] = 'sebelas';
            $number = 0;
        } elseif ($number >= 12 && $number <= 19) {
            $words[] = self::INDONESIAN_ONES[$number - 10].' belas';
            $number = 0;
        } elseif ($number >= 20) {
            $words[] = self::INDONESIAN_ONES[intdiv($number, 10)].' puluh';
            $number %= 10;
        }

        if ($number > 0) {
            $words[] = self::INDONESIAN_ONES[$number];
        }

        $chunk = implode(' ', $words);

        if ($scale === 0) {
            return $chunk;
        }

        return "{$chunk} ".self::INDONESIAN_SCALES[$scale];
    }
}
