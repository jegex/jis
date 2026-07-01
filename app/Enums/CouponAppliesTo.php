<?php

declare(strict_types=1);

namespace App\Enums;

enum CouponAppliesTo: string
{
    case All = 'all';
    case SpecificProduct = 'specific_product';
}
