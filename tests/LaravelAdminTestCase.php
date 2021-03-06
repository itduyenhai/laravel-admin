<?php

namespace SystemInc\LaravelAdmin\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use SystemInc\LaravelAdmin\Database\Seeds\DatabaseSeeder as DatabaseSeeder;

abstract class LaravelAdminTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database
    }

    protected function getPackageProviders($app)
    {
        return ['SystemInc\LaravelAdmin\AdminServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'SLA'    => \SystemInc\LaravelAdmin\Facades\SLA::class,
            'config' => Illuminate\Config\Repository::class,
        ];
    }

    public function setUp()
    {
        parent::setUp();

        Schema::defaultStringLength(191);

        Artisan::call('migrate', [
            '--path' => '../../../../src/database/migrations',
        ]);

        $seeder = new DatabaseSeeder();
        $seeder->run();

        $this->withFactories(__DIR__.'/../src/database/factories');
    }

    public function tearDown()
    {
        Artisan::call('migrate:reset', [
            '--path' => '../../../../src/database/migrations',
        ]);

        parent::tearDown();
    }
}
