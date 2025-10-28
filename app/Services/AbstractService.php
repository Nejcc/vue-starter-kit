<?php

namespace App\Services;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @template T of Model
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
     * @param  RepositoryInterface<T>  $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Execute a callback within a database transaction.
     *
     * @throws \Throwable
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Validate the given data against rules.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate(array $data, array $rules): array
    {
        return validator($data, $rules)->validate();
    }

    /**
     * Get the repository instance.
     *
     * @return RepositoryInterface<T>
     */
    protected function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
