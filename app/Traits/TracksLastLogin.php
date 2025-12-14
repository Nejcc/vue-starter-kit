<?php

declare(strict_types=1);

namespace App\Traits;

trait TracksLastLogin
{
    /**
     * Record the last login timestamp.
     */
    public function recordLastLogin(): void
    {
        $originalUpdatedAt = $this->getOriginal('updated_at') ?? $this->updated_at;

        $this->getConnection()
            ->table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->update([
                'last_login_at' => now(),
                'updated_at' => $originalUpdatedAt,
            ]);

        $this->last_login_at = now();
        $this->syncOriginal();
    }

    /**
     * Check if the user has ever logged in.
     */
    public function hasLoggedIn(): bool
    {
        return null !== $this->last_login_at;
    }
}
