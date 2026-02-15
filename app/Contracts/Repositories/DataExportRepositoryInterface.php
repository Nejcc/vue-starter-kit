<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;

interface DataExportRepositoryInterface
{
    /** @return array<string, mixed> */
    public function collectProfile(User $user): array;

    /** @return array<int, string> */
    public function collectRoles(User $user): array;

    /** @return array<int, string> */
    public function collectPermissions(User $user): array;

    /** @return array<int, array<string, mixed>> */
    public function collectNotifications(User $user): array;

    /** @return array<int, array<string, mixed>> */
    public function collectAuditLogs(User $user): array;

    /** @return array<int, array<string, mixed>> */
    public function collectSessions(User $user): array;

    /** @return array<string, mixed>|null */
    public function collectPaymentData(User $user): ?array;

    /** @return array<int, array<string, mixed>>|null */
    public function collectOrganizationData(User $user): ?array;

    /** @return array<string, mixed>|null */
    public function collectSubscriberData(User $user): ?array;
}
