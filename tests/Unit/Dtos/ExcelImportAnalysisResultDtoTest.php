<?php

declare(strict_types=1);

use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum;

test('ExcelImportAnalysisResultDto factory methods create expected levels', function () {
    $info = ExcelImportAnalysisResultDto::info('msg', 'code');
    $warning = ExcelImportAnalysisResultDto::warning('msg', 'code');
    $error = ExcelImportAnalysisResultDto::error('msg', 'code');
    $critical = ExcelImportAnalysisResultDto::critical('msg', 'code');

    expect($info->level)->toBe(ExcelImportAnalysisLevelEnum::INFO)
        ->and($warning->level)->toBe(ExcelImportAnalysisLevelEnum::WARNING)
        ->and($error->level)->toBe(ExcelImportAnalysisLevelEnum::ERROR)
        ->and($critical->level)->toBe(ExcelImportAnalysisLevelEnum::CRITICAL);
});

test('ExcelImportAnalysisResultDto can extend message and code', function () {
    $dto = ExcelImportAnalysisResultDto::error('Base', 'base-code');

    $dto->appendMessage('Extra');
    $dto->appendCode('extra');

    expect($dto->message)->toContain('Base')
        ->and($dto->message)->toContain('Extra')
        ->and($dto->uniqueCode)->toBe('base-code_extra');
});
