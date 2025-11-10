<?php

declare(strict_types=1);

namespace Tests\Unit;

use LaravelPlus\Installer\Support\Options;
use PHPUnit\Framework\TestCase;

final class OptionsTest extends TestCase
{
    public function test_from_input_defaults(): void
    {
        $options = Options::fromInput([]);
        $this->assertSame('dev', $options->mode);
        $this->assertFalse($options->nonInteractive);
        $this->assertFalse($options->cleanDependencies);
        $this->assertFalse($options->fresh);
        $this->assertFalse($options->telescope);
        $this->assertFalse($options->nightwatch);
        $this->assertFalse($options->dryRun);
        $this->assertSame('sqlite', $options->database);
    }

    public function test_from_input_flags(): void
    {
        $options = Options::fromInput([
            'mode' => 'prod',
            'yes' => true,
            'no-clean' => true,
            'fresh' => true,
            'telescope' => true,
            'nightwatch' => true,
            'dry' => true,
            'no-audit' => true,
            'no-build' => true,
            'timeout' => 10,
            'database' => 'mysql',
        ]);

        $this->assertSame('prod', $options->mode);
        $this->assertTrue($options->nonInteractive);
        $this->assertFalse($options->cleanDependencies);
        $this->assertTrue($options->fresh);
        $this->assertTrue($options->telescope);
        $this->assertTrue($options->nightwatch);
        $this->assertTrue($options->dryRun);
        $this->assertTrue($options->skipAudit);
        $this->assertTrue($options->skipBuild);
        $this->assertSame(10, $options->timeoutSeconds);
        $this->assertSame('mysql', $options->database);
    }
}
