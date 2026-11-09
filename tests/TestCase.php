<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Tests;

use Gbrain\ExcelImports\ExcelImportServiceProvider;
use Maatwebsite\Excel\ExcelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ExcelServiceProvider::class,
            ExcelImportServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // This package does not touch the database; disable Laravel Excel's
        // default DB transaction wrapper so imports run without a PDO driver.
        $app['config']->set('excel.transactions.handler', 'null');
    }
}
