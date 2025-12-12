<?php

declare(strict_types=1);

use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum as Level;

test('ExcelImportAnalysisLevelEnum compares severities correctly', function () {
    expect(Level::INFO->lt(Level::WARNING))->toBeTrue()
        ->and(Level::WARNING->lt(Level::ERROR))->toBeTrue()
        ->and(Level::ERROR->lt(Level::CRITICAL))->toBeTrue()
        ->and(Level::CRITICAL->gte(Level::ERROR))->toBeTrue()
        ->and(Level::INFO->lt(Level::WARNING))->toBeTrue()
        ->and(Level::WARNING->gt(Level::INFO))->toBeTrue()
        ->and(Level::ERROR->gte(Level::WARNING))->toBeTrue()
        ->and(Level::WARNING->lte(Level::WARNING))->toBeTrue()
        ->and(Level::CRITICAL->compareTo(Level::ERROR))->toBe(1)
        ->and(Level::ERROR->compareTo(Level::ERROR))->toBe(0)
        ->and(Level::WARNING->compareTo(Level::CRITICAL))->toBe(-1);
});
