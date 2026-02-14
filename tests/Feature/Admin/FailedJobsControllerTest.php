<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class FailedJobsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($superAdminRole);
    }

    private function createFailedJob(array $overrides = []): int
    {
        return DB::table('failed_jobs')->insertGetId(array_merge([
            'uuid' => Str::uuid()->toString(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => 'App\\Jobs\\ProcessPayment',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => ['command' => '{}'],
            ]),
            'exception' => "RuntimeException: Payment gateway timeout\n#0 /app/Jobs/ProcessPayment.php(42): ...\n#1 [internal function]",
            'failed_at' => now()->toDateTimeString(),
        ], $overrides));
    }

    public function test_guests_cannot_access_failed_jobs(): void
    {
        $this->get(route('admin.failed-jobs.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_failed_jobs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.failed-jobs.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_failed_jobs_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/FailedJobs/Index')
                ->has('failedJobs')
                ->has('queues')
                ->has('stats')
                ->has('filters')
            );
    }

    public function test_index_shows_failed_jobs(): void
    {
        $this->createFailedJob();
        $this->createFailedJob();

        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('failedJobs.total', 2)
                ->where('stats.total', 2)
            );
    }

    public function test_index_shows_empty_state(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('failedJobs.total', 0)
                ->where('stats.total', 0)
            );
    }

    public function test_index_extracts_job_name(): void
    {
        $this->createFailedJob([
            'payload' => json_encode([
                'displayName' => 'App\\Jobs\\SendEmail',
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => ['command' => '{}'],
            ]),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('failedJobs.data.0.job_name', 'SendEmail')
            );
    }

    public function test_index_can_search_by_queue(): void
    {
        $this->createFailedJob(['queue' => 'emails']);
        $this->createFailedJob(['queue' => 'payments']);

        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.index', ['search' => 'emails']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('failedJobs.total', 1)
            );
    }

    public function test_index_can_filter_by_queue(): void
    {
        $this->createFailedJob(['queue' => 'emails']);
        $this->createFailedJob(['queue' => 'payments']);

        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.index', ['queue' => 'emails']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('failedJobs.total', 1)
            );
    }

    public function test_index_returns_distinct_queues(): void
    {
        $this->createFailedJob(['queue' => 'emails']);
        $this->createFailedJob(['queue' => 'emails']);
        $this->createFailedJob(['queue' => 'payments']);

        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('stats.queues', 2)
            );
    }

    public function test_admin_can_view_failed_job_details(): void
    {
        $id = $this->createFailedJob();

        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.show', $id))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/FailedJobs/Show')
                ->has('job.id')
                ->has('job.uuid')
                ->has('job.connection')
                ->has('job.queue')
                ->has('job.job_name')
                ->has('job.payload')
                ->has('job.exception')
                ->has('job.failed_at')
            );
    }

    public function test_show_returns_404_for_nonexistent_job(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.show', 999))
            ->assertNotFound();
    }

    public function test_admin_can_retry_a_job(): void
    {
        $uuid = Str::uuid()->toString();
        $this->createFailedJob(['uuid' => $uuid]);

        $this->actingAs($this->admin)
            ->post(route('admin.failed-jobs.retry', $uuid))
            ->assertRedirect();
    }

    public function test_admin_can_retry_all_jobs(): void
    {
        $this->createFailedJob();
        $this->createFailedJob();

        $this->actingAs($this->admin)
            ->post(route('admin.failed-jobs.retry-all'))
            ->assertRedirect();
    }

    public function test_admin_can_delete_a_failed_job(): void
    {
        $id = $this->createFailedJob();

        $this->assertDatabaseCount('failed_jobs', 1);

        $this->actingAs($this->admin)
            ->delete(route('admin.failed-jobs.destroy', $id))
            ->assertRedirect();

        $this->assertDatabaseCount('failed_jobs', 0);
    }

    public function test_admin_can_flush_all_failed_jobs(): void
    {
        $this->createFailedJob();
        $this->createFailedJob();
        $this->createFailedJob();

        $this->assertDatabaseCount('failed_jobs', 3);

        $this->actingAs($this->admin)
            ->delete(route('admin.failed-jobs.destroy-all'))
            ->assertRedirect();

        $this->assertDatabaseCount('failed_jobs', 0);
    }

    public function test_failed_job_data_has_required_fields(): void
    {
        $this->createFailedJob();

        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('failedJobs.data.0', fn ($job) => $job
                    ->has('id')
                    ->has('uuid')
                    ->has('connection')
                    ->has('queue')
                    ->has('job_name')
                    ->has('exception_summary')
                    ->has('failed_at')
                )
            );
    }

    public function test_exception_summary_is_truncated(): void
    {
        $longException = str_repeat('A', 200) . "\nStack trace line";
        $this->createFailedJob(['exception' => $longException]);

        $this->actingAs($this->admin)
            ->get(route('admin.failed-jobs.index'))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $summary = $page->toArray()['props']['failedJobs']['data'][0]['exception_summary'];
                $this->assertLessThanOrEqual(153, mb_strlen($summary)); // 150 + "..."
            });
    }

    public function test_admin_role_can_access_failed_jobs(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->get(route('admin.failed-jobs.index'))
            ->assertOk();
    }
}
