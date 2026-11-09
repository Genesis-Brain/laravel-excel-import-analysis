<?php

declare(strict_types=1);

use Gbrain\ExcelImports\Tests\TestCase;

// Feature tests boot a Laravel application via Orchestra Testbench so that
// Laravel Excel's facade and service provider are available.
uses(TestCase::class)->in('Feature');
