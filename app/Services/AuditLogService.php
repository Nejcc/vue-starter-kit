<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AuditEvent;
use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Services\AuditLogServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;

final class AuditLogService extends AbstractService implements AuditLogServiceInterface
{
    public function __construct(AuditLogRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getFilteredPaginated(?string $search, ?string $event, int $perPage = 25): LengthAwarePaginator
    {
        return $this->getRepository()->getFilteredPaginated($search, $event, $perPage);
    }

    public function getDistinctEventTypes(): SupportCollection
    {
        return $this->getRepository()->getDistinctEventTypes();
    }

    public function getRecentWithUser(int $limit = 10): Collection
    {
        return $this->getRepository()->getRecentWithUser($limit);
    }

    public function getUserActivityPaginated(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->getRepository()->getUserActivityPaginated($userId, $perPage);
    }

    public function describeEvent(string $event): string
    {
        return match ($event) {
            AuditEvent::AUTH_LOGIN => 'Signed in',
            AuditEvent::AUTH_LOGOUT => 'Signed out',
            AuditEvent::AUTH_REGISTERED => 'Account created',
            AuditEvent::AUTH_PASSWORD_RESET => 'Password reset via email',
            AuditEvent::AUTH_EMAIL_VERIFIED => 'Email address verified',
            AuditEvent::AUTH_LOGIN_FAILED => 'Failed sign-in attempt',
            AuditEvent::USER_PROFILE_UPDATED => 'Profile information updated',
            AuditEvent::USER_PASSWORD_CHANGED => 'Password changed',
            AuditEvent::USER_ACCOUNT_DELETED => 'Account deleted',
            AuditEvent::USER_DATA_EXPORTED => 'Personal data exported',
            AuditEvent::IMPERSONATION_STARTED => 'Impersonation started',
            AuditEvent::IMPERSONATION_STOPPED => 'Impersonation stopped',
            default => ucfirst(str_replace(['.', '_'], ' ', $event)),
        };
    }
}
