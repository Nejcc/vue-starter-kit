<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface SystemHealthServiceInterface
{
    /** @return array<int, array<string, mixed>> */
    public function runAllChecks(): array;

    /** @return array<string, mixed> */
    public function getSystemInfo(): array;
}
