<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class PermissionException extends RuntimeException
{
    public static function cannotDeleteAssignedToRoles(int $roleCount): self
    {
        return new self("Cannot delete permission: it is assigned to {$roleCount} role(s). Remove this permission from all roles first.");
    }
}
