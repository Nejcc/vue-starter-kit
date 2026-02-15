<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AuditEvent;
use App\Contracts\Repositories\CacheManagementRepositoryInterface;
use App\Contracts\Services\CacheManagementServiceInterface;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

final class CacheManagementService extends AbstractNonModelService implements CacheManagementServiceInterface
{
    /** @var array<string, string> */
    private const CACHE_COMMANDS = [
        'cache' => 'cache:clear',
        'views' => 'view:clear',
        'routes' => 'route:clear',
        'config' => 'config:clear',
    ];

    public function __construct(
        private readonly CacheManagementRepositoryInterface $repository,
    ) {}

    /**
     * @return array{driver: array<string, mixed>, stats: array<string, mixed>, items: array<int, array<string, mixed>>, maintenance: array<string, mixed>}
     */
    public function getIndexData(): array
    {
        return [
            'driver' => $this->repository->getDriverInfo(),
            'stats' => $this->repository->getCacheStats(),
            'items' => $this->repository->getCacheItems(),
            'maintenance' => $this->repository->getMaintenanceStatus(),
        ];
    }

    public function clearCache(string $type): void
    {
        $command = self::CACHE_COMMANDS[$type] ?? null;

        if (!$command) {
            throw new InvalidArgumentException("Unknown cache type: {$type}");
        }

        $this->repository->clearArtisanCache($command);

        AuditLog::log(AuditEvent::CACHE_CLEARED, null, null, ['type' => $type]);
    }

    public function clearAllCaches(): void
    {
        $errors = $this->repository->clearAllCaches();

        if ($errors !== []) {
            throw new RuntimeException(implode('. ', $errors));
        }

        AuditLog::log(AuditEvent::CACHE_CLEARED, null, null, ['type' => 'all']);
    }

    /**
     * @return array{message: string}
     */
    public function toggleMaintenance(Request $request): array
    {
        $isDown = app()->isDownForMaintenance();

        if ($isDown) {
            $this->repository->disableMaintenance();
            AuditLog::log(AuditEvent::MAINTENANCE_TOGGLED, null, ['maintenance' => true], ['maintenance' => false]);

            return ['message' => 'Application is now live.'];
        }

        $secret = $request->input('secret');
        $params = [];

        if ($secret) {
            $params['--secret'] = $secret;
        }

        $this->repository->enableMaintenance($params);
        AuditLog::log(AuditEvent::MAINTENANCE_TOGGLED, null, ['maintenance' => false], ['maintenance' => true]);

        return ['message' => 'Application is now in maintenance mode.' . ($secret ? " Secret bypass: {$secret}" : '')];
    }
}
