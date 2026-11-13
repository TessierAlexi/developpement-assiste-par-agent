<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Don't require built frontend assets (public/build/manifest.json) to render views in tests.
        $this->withoutVite();
    }
}
