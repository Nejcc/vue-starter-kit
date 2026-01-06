<?php

declare(strict_types=1);

namespace App\Constants;

final class RoleNames
{
    /**
     * Super Admin role name.
     *
     * This role has all permissions automatically granted via Gate::before in AuthServiceProvider.
     * It should be protected from deletion and name changes.
     */
    public const SUPER_ADMIN = 'super-admin';

    /**
     * Admin role name.
     */
    public const ADMIN = 'admin';

    /**
     * User role name.
     */
    public const USER = 'user';
}
