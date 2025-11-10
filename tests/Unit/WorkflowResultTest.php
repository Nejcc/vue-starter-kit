<?php

declare(strict_types=1);

namespace Tests\Unit;

use LaravelPlus\Installer\Support\Results\StepResult;
use LaravelPlus\Installer\Support\Results\WorkflowResult;
use PHPUnit\Framework\TestCase;

final class WorkflowResultTest extends TestCase
{
    public function test_success_from_steps(): void
    {
        $r1 = StepResult::success('A');
        $r2 = StepResult::success('B');
        $w = WorkflowResult::fromSteps([$r1, $r2]);
        $this->assertTrue($w->successful);
        $this->assertNull($w->failedAt);
        $this->assertSame($r2, $w->lastResult());
    }

    public function test_failure_from_steps(): void
    {
        $r1 = StepResult::success('A');
        $r2 = StepResult::failure('B', 'boom');
        $r3 = StepResult::success('C');
        $w = WorkflowResult::fromSteps([$r1, $r2, $r3]);
        $this->assertFalse($w->successful);
        $this->assertSame('B', $w->failedAt);
        $this->assertSame($r3, $w->lastResult());
    }
}
