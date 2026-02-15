<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

final class SeoConfigTest extends TestCase
{
    public function test_seo_config_exists(): void
    {
        $config = config('seo');

        $this->assertIsArray($config);
    }

    public function test_seo_config_has_expected_keys(): void
    {
        $this->assertNotNull(config('seo.gtm_id'));
        $this->assertNotNull(config('seo.default_meta_description'));
        $this->assertNotNull(config('seo.default_robots'));
    }

    public function test_seo_config_defaults_are_correct(): void
    {
        $this->assertSame('', config('seo.gtm_id'));
        $this->assertSame('', config('seo.default_meta_description'));
        $this->assertSame('index, follow', config('seo.default_robots'));
    }
}
