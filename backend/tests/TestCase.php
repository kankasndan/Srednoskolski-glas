<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // phpunit.xml uses sqlite in-memory. Fall back to a dedicated MySQL
        // database when the sqlite PDO driver is not installed.
        if (! extension_loaded('pdo_sqlite')) {
            $app['config']->set('database.default', 'mysql');
            $app['config']->set('database.connections.mysql.database', 'srednoskolski_glas_testing');
            $app['config']->set('database.connections.mysql.url', '');
        }

        return $app;
    }
}
