<?php

namespace Thinktomorrow\Locale\Tests;

use Thinktomorrow\Url\UrlServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [UrlServiceProvider::class];
    }
}
