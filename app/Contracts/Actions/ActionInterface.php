<?php

namespace App\Contracts\Actions;

/**
 * Base action contract for single-purpose operations.
 */
interface ActionInterface
{
    /**
     * Execute the action.
     */
    public function handle(mixed ...$parameters): mixed;
}
