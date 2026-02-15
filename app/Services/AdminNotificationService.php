<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Contracts\Services\AdminNotificationServiceInterface;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

final class AdminNotificationService extends AbstractNonModelService implements AdminNotificationServiceInterface
{
    public function __construct(
        private readonly AdminNotificationRepositoryInterface $repository,
    ) {}

    /** @return array<string, mixed> */
    public function getIndexData(Request $request): array
    {
        $notifications = $this->repository->getPaginated(
            $request->get('search'),
            $request->get('filter'),
            $request->get('user_id'),
        )->through(fn (DatabaseNotification $notification) => [
            'id' => $notification->id,
            'type' => class_basename($notification->type),
            'data' => $notification->data,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
            'notifiable' => $notification->notifiable ? [
                'id' => $notification->notifiable->id,
                'name' => $notification->notifiable->name ?? 'Unknown',
                'email' => $notification->notifiable->email ?? '',
            ] : null,
        ]);

        $users = User::query()
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return [
            'notifications' => $notifications,
            'users' => $users,
            'stats' => $this->repository->getStats(),
            'filters' => [
                'search' => $request->get('search', ''),
                'filter' => $request->get('filter', 'all'),
                'user_id' => $request->get('user_id', ''),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function sendNotification(array $validated): int
    {
        $users = User::whereIn('id', $validated['user_ids'])->get();

        foreach ($users as $user) {
            $user->notify(new GeneralNotification(
                title: $validated['title'],
                body: $validated['body'],
                actionUrl: $validated['action_url'] ?? null,
            ));
        }

        return $users->count();
    }

    public function markAsRead(string $id): void
    {
        $this->repository->markAsRead($id);
    }

    public function deleteNotification(string $id): void
    {
        $this->repository->delete($id);
    }

    public function deleteAll(?string $filter): int
    {
        return $this->repository->deleteFiltered($filter);
    }
}
