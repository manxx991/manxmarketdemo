<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        if ($app->configurationIsCached()) {
            throw new \RuntimeException('Tests refuse cached configuration before database setup.');
        }

        $app->make(Kernel::class)->bootstrap();

        if (!$app->environment('testing')
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || !in_array(config('database.connections.sqlite.url'), [null, ''], true)) {
            throw new \RuntimeException('Tests require SQLite :memory: with an empty database URL in the testing environment.');
        }

        return $app;
    }
}
