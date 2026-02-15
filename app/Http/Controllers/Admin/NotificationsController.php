<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\AdminNotificationServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendNotificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationsController extends Controller
{
    public function __construct(
        private readonly AdminNotificationServiceInterface $notificationService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('admin/Notifications/Index', $this->notificationService->getIndexData($request));
    }

    public function send(SendNotificationRequest $request): RedirectResponse
    {
        $count = $this->notificationService->sendNotification($request->validated());

        return back()->with('success', "Notification sent to {$count} user(s).");
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->notificationService->deleteNotification($id);

        return back()->with('success', 'Notification deleted.');
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $this->notificationService->markAsRead($id);

        return back();
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $count = $this->notificationService->deleteAll($request->get('filter'));

        return back()->with('success', "Deleted {$count} notification(s).");
    }
}
