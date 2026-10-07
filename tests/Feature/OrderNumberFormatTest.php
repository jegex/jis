<?php

declare(strict_types=1);

use App\Services\OrderNumberFormat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders date and sequence tokens', function () {
    $number = OrderNumberFormat::render(
        'ORD-{YYYY}{MM}-{SEQ:M}',
        4,
        Carbon\CarbonImmutable::parse('2026-10-08'),
        42,
    );

    expect($number)->toBe('ORD-202610-0042');
});

it('renders short date tokens', function () {
    $number = OrderNumberFormat::render(
        'SHOP/{YY}/{DD}/{SEQ}',
        4,
        Carbon\CarbonImmutable::parse('2026-10-08'),
        7,
    );

    expect($number)->toBe('SHOP/26/08/0007');
});

it('pads the sequence to the configured length', function () {
    $number = OrderNumberFormat::render(
        '{SEQ}',
        6,
        Carbon\CarbonImmutable::parse('2026-10-08'),
        1,
    );

    expect($number)->toBe('000001');
});

it('accepts a valid pattern', function () {
    expect(OrderNumberFormat::validate('ORD-{YYYY}{MM}-{SEQ:M}'))->toBeNull();
});

it('rejects unknown tokens', function () {
    $error = OrderNumberFormat::validate('ORD-{FOO}');

    expect($error)->not->toBeNull()
        ->and($error)->toContain('{FOO}');
});

it('rejects unbalanced braces', function () {
    expect(OrderNumberFormat::validate('{SEQ}}'))->not->toBeNull()
        ->and(OrderNumberFormat::validate('{SEQ}-{SEQ'))->not->toBeNull()
        ->and(OrderNumberFormat::validate('{{SEQ}'))->not->toBeNull()
        ->and(OrderNumberFormat::validate('ORD-{}'))->not->toBeNull()
        ->and(OrderNumberFormat::validate('ORD-{YYYY'))->not->toBeNull();
});

it('rejects a pattern without a sequence token', function () {
    expect(OrderNumberFormat::validate('ORD-{YYYY}{MM}'))->not->toBeNull();
});

it('rejects multiple sequence tokens', function () {
    expect(OrderNumberFormat::validate('{SEQ}-{SEQ:M}'))->not->toBeNull();
});

it('rejects a pattern that renders longer than the column', function () {
    expect(OrderNumberFormat::validate(str_repeat('A', 60).'{SEQ}'))->not->toBeNull();
});

it('rejects padding outside the allowed range', function () {
    expect(OrderNumberFormat::validate('{SEQ}', 0))->not->toBeNull()
        ->and(OrderNumberFormat::validate('{SEQ}', 13))->not->toBeNull();
});

it('scopes a monthly counter per month', function () {
    $key = OrderNumberFormat::counterKey(
        'ORD-{YYYY}{MM}-{SEQ:M}',
        Carbon\CarbonImmutable::parse('2026-10-08'),
    );

    expect($key)->toBe('seq:M:2026-10');
});

it('scopes a yearly counter per year', function () {
    $key = OrderNumberFormat::counterKey(
        'SHOP/{YYYY}/{SEQ:Y}',
        Carbon\CarbonImmutable::parse('2026-10-08'),
    );

    expect($key)->toBe('seq:Y:2026');
});

it('uses a single counter for a global sequence', function () {
    $key = OrderNumberFormat::counterKey(
        'ORD-{SEQ}',
        Carbon\CarbonImmutable::parse('2026-10-08'),
    );

    expect($key)->toBe('seq');
});
