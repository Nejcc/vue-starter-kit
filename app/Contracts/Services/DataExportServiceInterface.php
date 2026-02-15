<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;

interface DataExportServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function compileExportData(User $user): array;
}
