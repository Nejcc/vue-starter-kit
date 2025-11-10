<?php

namespace App\Services;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Abstract service implementation providing business logic layer.
 *
 * This service implements the Service Pattern, providing a layer for business
 * logic that sits between controllers and repositories. It includes transaction
 * support and validation helpers.
 *
 * @template T of Model
 *
 * @example
 * class UserService extends AbstractService
 * {
 *     public function __construct(UserRepositoryInterface $repository)
 *     {
 *         parent::__construct($repository);
 *     }
 * }
 */
abstract class AbstractService
{
    /**
     * The repository instance.
     *
     * @var RepositoryInterface<T>
     */
    protected RepositoryInterface $repository;

    /**
     * Create a new service instance.
     *
     * @param  RepositoryInterface<T>  $repository  The repository instance
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Execute a callback within a database transaction.
     *
     * Wraps the callback in a database transaction. If an exception is thrown,
     * the transaction is automatically rolled back. If the callback completes
     * successfully, the transaction is committed.
     *
     * @param  callable  $callback  The callback to execute within the transaction
     * @return mixed The return value of the callback
     *
     * @throws \Throwable Any exception thrown by the callback
     *
     * @example
     * $result = $this->transaction(function () {
     *     $user = $this->repository->create([...]);
     *     // Additional operations...
     *     return $user;
     * });
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Validate the given data against rules.
     *
     * Validates the provided data using Laravel's validator. Returns only
     * the validated data. Throws ValidationException if validation fails.
     *
     * @param  array<string, mixed>  $data  The data to validate
     * @param  array<string, mixed>  $rules  The validation rules
     * @return array<string, mixed> The validated data
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails
     *
     * @example
     * $validated = $this->validate($data, [
     *     'name' => ['required', 'string', 'max:255'],
     *     'email' => ['required', 'email'],
     * ]);
     */
    protected function validate(array $data, array $rules): array
    {
        return validator($data, $rules)->validate();
    }

    /**
     * Get the repository instance.
     *
     * Returns the repository instance for direct access when needed.
     *
     * @return RepositoryInterface<T> The repository instance
     *
     * @example
     * $user = $this->getRepository()->find($id);
     */
    protected function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
