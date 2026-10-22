<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CI does not build front-end assets, so the Vite manifest does not exist.
        $this->withoutVite();
    }
}
