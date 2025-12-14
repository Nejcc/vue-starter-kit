<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class InstallerWorkflowTest extends TestCase
{
    public function test_runs_steps_until_failure(): void
    {
        $this->markTestSkipped('LaravelPlus installer classes not available');
    }
}
