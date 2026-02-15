<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Abstract base class for repositories that don't back an Eloquent model.
 *
 * Use this for repositories that interact with raw database tables,
 * facades, or system-level operations rather than Eloquent models.
 */
abstract class AbstractNonModelRepository {}
