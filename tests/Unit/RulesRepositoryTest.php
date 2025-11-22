<?php

declare(strict_types=1);

use Gbrain\ExcelImports\Abstracts\Analysis\ExcelImportAnalysisRule;
use Gbrain\ExcelImports\Abstracts\Analysis\ExcelImportAnalysisRulesRepository;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalyzesDataInterface;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalysisRulesRepositoryInterface;
use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum as Level;
use Illuminate\Support\Collection;

class DummyImport implements ExcelImportAnalyzesDataInterface
{
    use Gbrain\ExcelImports\Concerns\AnalyzesData\AnalyzesData;

    protected function handleRowImport(Collection $row): void {}
}

class DummyRule extends ExcelImportAnalysisRule
{
    protected function getRowAnalysis(Collection $row, int $rowIndex): ?ExcelImportAnalysisResultDto
    {
        if (($row['fail'] ?? false) === true) {
            return new ExcelImportAnalysisResultDto(
                level: Level::ERROR,
                message: 'failed',
                uniqueCode: 'DUMMY_FAIL',
            );
        }

        return null;
    }
}

it('registers and retrieves rules, and propagates minimal level', function () {
    /** @var ExcelImportAnalyzesDataInterface $import */
    $import = new DummyImport();
    $repo = (new ExcelImportAnalysisRulesRepository($import))
        ->minimalLevelToReport(Level::WARNING);

    expect($repo->getMinimalReportLevel())->toBe(Level::WARNING);

    $rule = (new DummyRule());
    $repo->add($rule);

    expect($repo->find(DummyRule::class))->toBeInstanceOf(DummyRule::class)
        ->and($repo->rules())->toHaveCount(1);
});
