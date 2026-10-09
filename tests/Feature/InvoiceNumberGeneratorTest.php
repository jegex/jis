<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\NumberCounter;
use App\Models\Setting;
use App\Services\InvoiceNumberGenerator;
use App\Services\OrderNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates the first invoice number of the month', function () {
    $number = app(InvoiceNumberGenerator::class)->generate(now());

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

    $number = app(InvoiceNumberGenerator::class)->generate(now());

    expect($number)->toBe($prefix.'/0004');
});

it('resets the sequence on a new month', function () {
    Invoice::factory()->create([
        'number' => 'INV/'.now()->subMonthNoOverflow()->format('Y').'/'.now()->subMonthNoOverflow()->format('m').'/0042',
    ]);

    $number = app(InvoiceNumberGenerator::class)->generate(now());

    expect($number)->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0001');
});

it('continues the sequence for a past month', function () {
    Invoice::factory()->create([
        'number' => 'INV/2026/01/0007',
        'issued_at' => Carbon\CarbonImmutable::parse('2026-01-15'),
    ]);

    $number = app(InvoiceNumberGenerator::class)->generate(Carbon\CarbonImmutable::parse('2026-01-20'));

    expect($number)->toBe('INV/2026/01/0008');
});

it('applies the configured format and padding', function () {
    Setting::set('invoice_number_format', 'INV-{YYYY}-{SEQ:Y}');
    Setting::set('invoice_number_padding', 6);

    $number = app(InvoiceNumberGenerator::class)->generate(Carbon\CarbonImmutable::parse('2026-01-05'));

    expect($number)->toBe('INV-2026-000001');
});

it('never resets a global sequence', function () {
    Setting::set('invoice_number_format', 'INV-{SEQ}');

    $generator = app(InvoiceNumberGenerator::class);

    expect($generator->generate(Carbon\CarbonImmutable::parse('2026-01-05')))->toBe('INV-0001')
        ->and($generator->generate(Carbon\CarbonImmutable::parse('2026-06-05')))->toBe('INV-0002');
});

it('resets a yearly sequence on a new year', function () {
    Setting::set('invoice_number_format', 'INV-{YYYY}-{SEQ:Y}');

    $generator = app(InvoiceNumberGenerator::class);

    expect($generator->generate(Carbon\CarbonImmutable::parse('2026-02-05')))->toBe('INV-2026-0001')
        ->and($generator->generate(Carbon\CarbonImmutable::parse('2026-12-05')))->toBe('INV-2026-0002')
        ->and($generator->generate(Carbon\CarbonImmutable::parse('2027-01-05')))->toBe('INV-2027-0001');
});

it('skips an invoice number that already exists', function () {
    Invoice::factory()->create([
        'number' => 'INV/'.now()->format('Y').'/'.now()->format('m').'/0001',
    ]);

    expect(app(InvoiceNumberGenerator::class)->generate(now()))
        ->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0002');
});

it('reconciles a higher existing invoice number even when the counter already exists', function () {
    NumberCounter::factory()->create([
        'key' => 'invoice:seq:M:'.now()->format('Y-m'),
        'value' => 1,
    ]);

    Invoice::factory()->create([
        'number' => 'INV/'.now()->format('Y').'/'.now()->format('m').'/0005',
    ]);

    expect(app(InvoiceNumberGenerator::class)->generate(now()))
        ->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0006');
});

it('previews the same number that will be generated when an existing number is higher', function () {
    NumberCounter::factory()->create([
        'key' => 'invoice:seq:M:'.now()->format('Y-m'),
        'value' => 1,
    ]);

    Invoice::factory()->create([
        'number' => 'INV/'.now()->format('Y').'/'.now()->format('m').'/0005',
    ]);

    $generator = app(InvoiceNumberGenerator::class);

    expect($generator->preview(1)[0])->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0006')
        ->and($generator->generate(now()))->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0006');
});

it('falls back to the default format when the saved format is invalid', function () {
    Setting::set('invoice_number_format', 'INV-{UNKNOWN}');

    expect(app(InvoiceNumberGenerator::class)->generate(now()))
        ->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0001');
});

it('continues the sequence when the format changes', function () {
    NumberCounter::factory()->create([
        'key' => 'invoice:seq:M:'.now()->format('Y-m'),
        'value' => 41,
    ]);

    expect(app(InvoiceNumberGenerator::class)->generate(now()))
        ->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0042');
});

it('previews the next invoice numbers without consuming the sequence', function () {
    $generator = app(InvoiceNumberGenerator::class);

    expect($generator->preview(3))->toBe([
        'INV/'.now()->format('Y').'/'.now()->format('m').'/0001',
        'INV/'.now()->format('Y').'/'.now()->format('m').'/0002',
        'INV/'.now()->format('Y').'/'.now()->format('m').'/0003',
    ]);

    expect($generator->generate(now()))->toBe('INV/'.now()->format('Y').'/'.now()->format('m').'/0001');
});

it('previews using a pattern and padding that are not saved yet', function () {
    $preview = app(InvoiceNumberGenerator::class)->preview(
        count: 2,
        pattern: 'INV/{YYYY}-{SEQ:Y}',
        padding: 3,
    );

    expect($preview)->toBe([
        'INV/'.now()->format('Y').'-001',
        'INV/'.now()->format('Y').'-002',
    ]);
});

it('keeps invoice counters separate from order counters', function () {
    Setting::set('order_number_format', 'ORD-{SEQ}');
    Setting::set('invoice_number_format', 'INV/{SEQ}');

    app(OrderNumberGenerator::class)->generate();

    expect(app(InvoiceNumberGenerator::class)->generate(now()))->toBe('INV/0001');
});
