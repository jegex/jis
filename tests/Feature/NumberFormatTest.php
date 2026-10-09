<?php

declare(strict_types=1);

use App\Services\NumberFormat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders date and sequence tokens', function () {
    $number = NumberFormat::render(
        'ORD-{YYYY}{MM}-{SEQ:M}',
        4,
        Carbon\CarbonImmutable::parse('2026-10-08'),
        42,
    );

    expect($number)->toBe('ORD-202610-0042');
});

it('renders short date tokens', function () {
    $number = NumberFormat::render(
        'SHOP/{YY}/{DD}/{SEQ}',
        4,
        Carbon\CarbonImmutable::parse('2026-10-08'),
        7,
    );

    expect($number)->toBe('SHOP/26/08/0007');
});

it('pads the sequence to the configured length', function () {
    $number = NumberFormat::render(
        '{SEQ}',
        6,
        Carbon\CarbonImmutable::parse('2026-10-08'),
        1,
    );

    expect($number)->toBe('000001');
});

it('accepts a valid pattern', function () {
    expect(NumberFormat::validate('ORD-{YYYY}{MM}-{SEQ:M}'))->toBeNull();
});

it('rejects unknown tokens', function () {
    $error = NumberFormat::validate('ORD-{FOO}');

    expect($error)->not->toBeNull()
        ->and($error)->toContain('{FOO}');
});

it('rejects unbalanced braces', function () {
    expect(NumberFormat::validate('{SEQ}}'))->not->toBeNull()
        ->and(NumberFormat::validate('{SEQ}-{SEQ'))->not->toBeNull()
        ->and(NumberFormat::validate('{{SEQ}'))->not->toBeNull()
        ->and(NumberFormat::validate('ORD-{}'))->not->toBeNull()
        ->and(NumberFormat::validate('ORD-{YYYY'))->not->toBeNull();
});

it('rejects a pattern without a sequence token', function () {
    expect(NumberFormat::validate('ORD-{YYYY}{MM}'))->not->toBeNull();
});

it('rejects multiple sequence tokens', function () {
    expect(NumberFormat::validate('{SEQ}-{SEQ:M}'))->not->toBeNull();
});

it('rejects a pattern that renders longer than the column', function () {
    expect(NumberFormat::validate(str_repeat('A', 60).'{SEQ}'))->not->toBeNull();
});

it('rejects padding outside the allowed range', function () {
    expect(NumberFormat::validate('{SEQ}', 0))->not->toBeNull()
        ->and(NumberFormat::validate('{SEQ}', 13))->not->toBeNull();
});

it('scopes a monthly counter per month', function () {
    $key = NumberFormat::counterKey(
        'ORD-{YYYY}{MM}-{SEQ:M}',
        Carbon\CarbonImmutable::parse('2026-10-08'),
    );

    expect($key)->toBe('seq:M:2026-10');
});

it('scopes a yearly counter per year', function () {
    $key = NumberFormat::counterKey(
        'SHOP/{YYYY}/{SEQ:Y}',
        Carbon\CarbonImmutable::parse('2026-10-08'),
    );

    expect($key)->toBe('seq:Y:2026');
});

it('uses a single counter for a global sequence', function () {
    $key = NumberFormat::counterKey(
        'ORD-{SEQ}',
        Carbon\CarbonImmutable::parse('2026-10-08'),
    );

    expect($key)->toBe('seq');
});
