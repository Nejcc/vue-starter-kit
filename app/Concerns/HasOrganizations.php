<?php

declare(strict_types=1);

namespace App\Concerns;

if (trait_exists(\LaravelPlus\Tenants\Traits\HasOrganizations::class)) {
    trait HasOrganizations
    {
        use \LaravelPlus\Tenants\Traits\HasOrganizations;
    }
} else {
    trait HasOrganizations
    {
    }
}
