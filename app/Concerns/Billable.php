<?php

declare(strict_types=1);

namespace App\Concerns;

if (trait_exists(\LaravelPlus\PaymentGateway\Traits\Billable::class)) {
    trait Billable
    {
        use \LaravelPlus\PaymentGateway\Traits\Billable;
    }
} else {
    trait Billable
    {
    }
}
