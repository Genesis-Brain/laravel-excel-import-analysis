<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Tests;

use Gbrain\ExcelImports\ExcelImportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ExcelImportServiceProvider::class,
        ];
    }
}
