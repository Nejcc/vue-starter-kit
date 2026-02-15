<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AuditEvent;
use App\Contracts\Repositories\DataExportRepositoryInterface;
use App\Contracts\Services\DataExportServiceInterface;
use App\Models\AuditLog;
use App\Models\User;

final class DataExportService extends AbstractNonModelService implements DataExportServiceInterface
{
    public function __construct(
        private readonly DataExportRepositoryInterface $repository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function compileExportData(User $user): array
    {
        $data = [
            'export_metadata' => [
                'exported_at' => now()->toIso8601String(),
                'version' => '1.0',
                'user_id' => $user->id,
            ],
            'profile' => $this->repository->collectProfile($user),
            'roles' => $this->repository->collectRoles($user),
            'permissions' => $this->repository->collectPermissions($user),
            'notifications' => $this->repository->collectNotifications($user),
            'audit_logs' => $this->repository->collectAuditLogs($user),
            'sessions' => $this->repository->collectSessions($user),
        ];

        $paymentData = $this->repository->collectPaymentData($user);
        if ($paymentData !== null) {
            $data['payment_data'] = $paymentData;
        }

        $organizationData = $this->repository->collectOrganizationData($user);
        if ($organizationData !== null) {
            $data['organizations'] = $organizationData;
        }

        $subscriberData = $this->repository->collectSubscriberData($user);
        if ($subscriberData !== null) {
            $data['subscriber_data'] = $subscriberData;
        }

        AuditLog::log(AuditEvent::USER_DATA_EXPORTED, $user, null, null, $user->id);

        return $data;
    }
}
