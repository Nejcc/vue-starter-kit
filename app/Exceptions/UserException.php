<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class UserException extends RuntimeException
{
    public static function cannotDeleteOwnAccount(): self
    {
        return new self('You cannot delete your own account.');
    }

    public static function cannotSuspendOwnAccount(): self
    {
        return new self('You cannot suspend your own account.');
    }
}
