<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\DataExportRepositoryInterface;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DataExportRepository extends AbstractNonModelRepository implements DataExportRepositoryInterface
{
    /** @return array<string, mixed> */
    public function collectProfile(User $user): array
    {
        $profile = $user->toArray();

        $sensitiveFields = [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        foreach ($sensitiveFields as $field) {
            unset($profile[$field]);
        }

        return $profile;
    }

    /** @return array<int, string> */
    public function collectRoles(User $user): array
    {
        return $user->getRoleNames()->toArray();
    }

    /** @return array<int, string> */
    public function collectPermissions(User $user): array
    {
        return $user->getAllPermissions()->pluck('name')->toArray();
    }

    /** @return array<int, array<string, mixed>> */
    public function collectNotifications(User $user): array
    {
        return DB::table('notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (object $notification): array => [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => json_decode($notification->data, true),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ])
            ->toArray();
    }

    /** @return array<int, array<string, mixed>> */
    public function collectAuditLogs(User $user): array
    {
        return AuditLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (AuditLog $log): array => [
                'id' => $log->id,
                'event' => $log->event,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'created_at' => $log->created_at?->toIso8601String(),
            ])
            ->toArray();
    }

    /** @return array<int, array<string, mixed>> */
    public function collectSessions(User $user): array
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->get()
            ->map(fn (object $session): array => [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity' => $session->last_activity,
            ])
            ->toArray();
    }

    /** @return array<string, mixed>|null */
    public function collectPaymentData(User $user): ?array
    {
        if (!class_exists(\LaravelPlus\PaymentGateway\Models\Transaction::class)) {
            return null;
        }

        return [
            'transactions' => $user->transactions()->get()->map(fn ($t): array => [
                'id' => $t->id,
                'amount' => $t->amount,
                'currency' => $t->currency,
                'status' => $t->status,
                'created_at' => $t->created_at?->toIso8601String(),
            ])->toArray(),
            'subscriptions' => $user->subscriptions()->get()->map(fn ($s): array => [
                'id' => $s->id,
                'status' => $s->status,
                'current_period_start' => $s->current_period_start,
                'current_period_end' => $s->current_period_end,
                'created_at' => $s->created_at?->toIso8601String(),
            ])->toArray(),
        ];
    }

    /** @return array<int, array<string, mixed>>|null */
    public function collectOrganizationData(User $user): ?array
    {
        if (!class_exists(\LaravelPlus\Tenants\Models\Organization::class)) {
            return null;
        }

        return $user->organizations()->get()->map(fn ($org): array => [
            'id' => $org->id,
            'name' => $org->name,
            'slug' => $org->slug,
            'role' => $org->pivot->role ?? null,
            'joined_at' => $org->pivot->joined_at ?? null,
        ])->toArray();
    }

    /** @return array<string, mixed>|null */
    public function collectSubscriberData(User $user): ?array
    {
        if (!class_exists(\LaravelPlus\Subscribe\Models\Subscriber::class)) {
            return null;
        }

        $subscriber = \LaravelPlus\Subscribe\Models\Subscriber::query()
            ->where('email', $user->email)
            ->first();

        if (!$subscriber) {
            return null;
        }

        return [
            'email' => $subscriber->email,
            'status' => $subscriber->status,
            'subscribed_at' => $subscriber->created_at?->toIso8601String(),
            'lists' => $subscriber->lists()->pluck('name')->toArray(),
        ];
    }
}
