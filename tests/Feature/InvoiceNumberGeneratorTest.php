<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates the first invoice number of the month', function () {
    $number = app(InvoiceNumberGenerator::class)->next(now());

    expect($number)->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0001');
});

it('increments the sequence within the same month', function () {
    $prefix = 'INV/'.now()->format('Y').'/'.now()->format('m');

    Invoice::factory()
        ->sequence(fn ($sequence) => [
            'number' => sprintf('%s/%04d', $prefix, $sequence->index + 1),
            'issued_at' => now(),
        ])
        ->count(3)
        ->create();

    $number = app(InvoiceNumberGenerator::class)->next(now());

    expect($number)->toBe($prefix.'/0004');
});

it('resets the sequence on a new month', function () {
    Invoice::factory()->create([
        'number' => 'INV/'.now()->subMonthNoOverflow()->format('Y').'/'.now()->subMonthNoOverflow()->format('m').'/0042',
    ]);

    $number = app(InvoiceNumberGenerator::class)->next(now());

    expect($number)->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0001');
});

it('continues the sequence for a past month', function () {
    Invoice::factory()->create([
        'number' => 'INV/2026/01/0007',
        'issued_at' => Carbon\CarbonImmutable::parse('2026-01-15'),
    ]);

    $number = app(InvoiceNumberGenerator::class)->next(Carbon\CarbonImmutable::parse('2026-01-20'));

    expect($number)->toBe('INV/2026/01/0008');
});
