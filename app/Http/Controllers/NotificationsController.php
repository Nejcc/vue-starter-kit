<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\NotificationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for user notifications.
 */
final class NotificationsController extends Controller
{
    public function __construct(
        private readonly NotificationServiceInterface $notificationService,
    ) {}

    /**
     * Display the notifications page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $filter = $request->get('filter');

        $notifications = $this->notificationService->getAllPaginated(
            $user,
            in_array($filter, ['read', 'unread'], true) ? $filter : null,
        );

        return Inertia::render('notifications/Index', [
            'notifications' => $notifications,
            'filter' => $filter ?? 'all',
            'unreadCount' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Get recent notifications for dropdown (JSON).
     */
    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'notifications' => $this->notificationService->getRecent($user),
            'unreadCount' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $this->notificationService->markAsRead($request->user(), $id);

        return back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return back();
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $this->notificationService->delete($request->user(), $id);

        return back();
    }
}
