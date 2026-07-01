<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Currency;

final class CurrencyController extends Controller
{
    public function __invoke(Currency $currency)
    {
        session(['currency' => $currency->code]);

        return redirect()->back();
    }
}
