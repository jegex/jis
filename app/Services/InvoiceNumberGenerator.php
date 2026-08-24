<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class InvoiceNumberGenerator
{
    public function next(CarbonInterface $date): string
    {
        $prefix = sprintf('INV/%s/%s', $date->format('Y'), $date->format('m'));

        return DB::transaction(function () use ($prefix) {
            Invoice::query()
                ->where('number', 'like', $prefix.'/%')
                ->lockForUpdate()
                ->first();

            $lastNumber = (string) Invoice::query()
                ->where('number', 'like', $prefix.'/%')
                ->orderByDesc('number')
                ->value('number');

            $sequence = $lastNumber === ''
                ? 0
                : (int) Str::afterLast($lastNumber, '/');

            return sprintf('%s/%04d', $prefix, $sequence + 1);
        });
    }
}
