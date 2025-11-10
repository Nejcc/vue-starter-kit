<?php

declare(strict_types=1);

namespace Tests\Unit;

use LaravelPlus\Installer\Support\Contracts\ProgressReporter;
use LaravelPlus\Installer\Support\Options;
use LaravelPlus\Installer\Support\Results\StepResult;
use LaravelPlus\Installer\Workflow\InstallerWorkflow;
use LaravelPlus\Installer\Workflow\Steps\InstallerStep;
use PHPUnit\Framework\TestCase;

final class InstallerWorkflowTest extends TestCase
{
    public function test_runs_steps_until_failure(): void
    {
        $reporter = new class implements ProgressReporter
        {
            public function section(string $title): void {}

            public function info(string $message): void {}

            public function warn(string $message): void {}

            public function error(string $message): void {}

            public function stepStarted(string $name): void {}

            public function stepSucceeded(StepResult $result): void {}

            public function stepFailed(StepResult $result): void {}
        };

        $steps = [
            new class implements InstallerStep
            {
                public function name(): string
                {
                    return 'S1';
                }

                public function run(Options $options): StepResult
                {
                    return StepResult::success('S1');
                }
            },
            new class implements InstallerStep
            {
                public function name(): string
                {
                    return 'S2';
                }

                public function run(Options $options): StepResult
                {
                    return StepResult::failure('S2', 'x');
                }
            },
            new class implements InstallerStep
            {
                public function name(): string
                {
                    return 'S3';
                }

                public function run(Options $options): StepResult
                {
                    return StepResult::success('S3');
                }
            },
        ];

        $workflow = new InstallerWorkflow($reporter, $steps);
        $result = $workflow->run(new Options);

        $this->assertFalse($result->successful);
        $this->assertSame('S2', $result->failedAt);
        $this->assertCount(2, $result->steps);
    }
}
