<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Traits\TracksLastLogin;
use Illuminate\Auth\Events\Login;

final class UpdateLastLoginAt
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (in_array(TracksLastLogin::class, class_uses_recursive($user), true)) {
            $user->recordLastLogin();
        }
    }
}
