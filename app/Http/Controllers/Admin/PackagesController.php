<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\AuditEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePackageRequest;
use App\Models\AuditLog;
use App\Services\PackageManagerService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PackagesController extends Controller
{
    public function __construct(
        private readonly PackageManagerService $packageManager,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/Packages/Index', [
            'packages' => $this->packageManager->getAll(),
        ]);
    }

    public function update(UpdatePackageRequest $request, string $key): RedirectResponse
    {
        if (!$this->packageManager->exists($key)) {
            abort(404);
        }

        if ($this->packageManager->isRequired($key)) {
            return redirect()->route('admin.packages.index')
                ->with('error', 'This package is required and cannot be disabled.');
        }

        $enabled = (bool) $request->validated('enabled');
        $oldEnabled = $this->packageManager->isEnabled($key);

        $this->packageManager->setEnabled($key, $enabled);

        AuditLog::log(
            AuditEvent::PACKAGE_TOGGLED,
            null,
            ['enabled' => $oldEnabled],
            ['enabled' => $enabled, 'package' => $key],
        );

        $status = $enabled ? 'enabled' : 'disabled';

        return redirect()->route('admin.packages.index')
            ->with('status', "Package {$key} has been {$status}.");
    }
}
