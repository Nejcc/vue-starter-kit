<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Abstract service for non-model-backed services.
 *
 * Mirrors AbstractService with transaction() and validate() helpers
 * but does not require a RepositoryInterface dependency.
 */
abstract class AbstractNonModelService
{
    /**
     * Execute a callback within a database transaction.
     *
     * @param  callable  $callback  The callback to execute within the transaction
     * @return mixed The return value of the callback
     *
     * @throws Throwable Any exception thrown by the callback
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Validate the given data against rules.
     *
     * @param  array<string, mixed>  $data  The data to validate
     * @param  array<string, mixed>  $rules  The validation rules
     * @return array<string, mixed> The validated data
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails
     */
    protected function validate(array $data, array $rules): array
    {
        return validator($data, $rules)->validate();
    }
}
