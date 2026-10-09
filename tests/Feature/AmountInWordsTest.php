<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Services\AmountInWords;

function ensureUsdCurrency(): Currency
{
    return Currency::query()->firstOrCreate(['code' => 'USD'], [
        'name' => 'US Dollar',
        'symbol' => '$',
        'exchange_rate' => 1,
        'decimal_place' => 2,
        'is_default' => false,
    ]);
}

it('converts an amount to words in english', function (float $amount, string $code, string $expected) {
    $code === 'IDR' ? ensureIdrCurrency() : ensureUsdCurrency();

    expect(app(AmountInWords::class)->toWords($amount, $code, 'en'))->toBe($expected);
})->with([
    'zero' => [0.0, 'USD', 'Zero US Dollar (USD)'],
    'one' => [1.0, 'USD', 'One US Dollar (USD)'],
    'teens' => [13.0, 'USD', 'Thirteen US Dollar (USD)'],
    'tens' => [21.0, 'USD', 'Twenty-One US Dollar (USD)'],
    'hundred' => [100.0, 'USD', 'One Hundred US Dollar (USD)'],
    'thousand' => [1000.0, 'USD', 'One Thousand US Dollar (USD)'],
    'hundred fifty thousand' => [150000.0, 'USD', 'One Hundred Fifty Thousand US Dollar (USD)'],
    'million' => [1000000.0, 'USD', 'One Million US Dollar (USD)'],
    'twenty million' => [20000000.0, 'USD', 'Twenty Million US Dollar (USD)'],
    'with cents' => [1234.56, 'USD', 'One Thousand Two Hundred Thirty-Four US Dollar and Fifty-Six Cents (USD)'],
    'whole with zero cents' => [100.0, 'USD', 'One Hundred US Dollar (USD)'],
]);

it('converts an amount to words in indonesian', function (float $amount, string $expected) {
    ensureIdrCurrency();

    expect(app(AmountInWords::class)->toWords($amount, 'IDR', 'id'))->toBe($expected);
})->with([
    'zero' => [0.0, 'Nol Indonesian Rupiah (IDR)'],
    'one' => [1.0, 'Satu Indonesian Rupiah (IDR)'],
    'ten' => [10.0, 'Sepuluh Indonesian Rupiah (IDR)'],
    'eleven' => [11.0, 'Sebelas Indonesian Rupiah (IDR)'],
    'twelve' => [12.0, 'Dua belas Indonesian Rupiah (IDR)'],
    'seventeen' => [17.0, 'Tujuh belas Indonesian Rupiah (IDR)'],
    'nineteen' => [19.0, 'Sembilan belas Indonesian Rupiah (IDR)'],
    'tens' => [21.0, 'Dua puluh satu Indonesian Rupiah (IDR)'],
    'large with teens' => [150012.0, 'Seratus lima puluh ribu dua belas Indonesian Rupiah (IDR)'],
    'hundred' => [100.0, 'Seratus Indonesian Rupiah (IDR)'],
    'thousand' => [1000.0, 'Seribu Indonesian Rupiah (IDR)'],
    'hundred fifty thousand' => [150000.0, 'Seratus lima puluh ribu Indonesian Rupiah (IDR)'],
    'million' => [1000000.0, 'Satu juta Indonesian Rupiah (IDR)'],
    'twenty million' => [20000000.0, 'Dua puluh juta Indonesian Rupiah (IDR)'],
]);

it('respects the currency decimal places', function () {
    ensureIdrCurrency();
    ensureUsdCurrency();

    expect(app(AmountInWords::class)->toWords(100.5, 'USD', 'en'))
        ->toBe('One Hundred US Dollar and Fifty Cents (USD)')
        ->and(app(AmountInWords::class)->toWords(100.5, 'IDR', 'en'))
        ->toBe('One Hundred One Indonesian Rupiah (IDR)');
});
