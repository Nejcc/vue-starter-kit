<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class RoleException extends RuntimeException
{
    public static function cannotCreateSuperAdmin(): self
    {
        return new self('The super-admin role cannot be created. It is a system role.');
    }

    public static function cannotRenameSuperAdmin(): self
    {
        return new self('The super-admin role name cannot be changed.');
    }

    public static function cannotUseSuperAdminName(): self
    {
        return new self('The super-admin role name cannot be used. It is a system role.');
    }

    public static function cannotDeleteSuperAdmin(): self
    {
        return new self('The super-admin role cannot be deleted. It is a system role with all permissions.');
    }

    public static function cannotDeleteWithUsers(int $userCount): self
    {
        return new self("Cannot delete role: it is assigned to {$userCount} user(s). Remove users from this role first.");
    }

    public static function cannotModifySuperAdminPermissions(): self
    {
        return new self('The super-admin role has all permissions automatically. Permissions cannot be modified.');
    }
}
