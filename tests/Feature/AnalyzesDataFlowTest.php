<?php

declare(strict_types=1);

use Gbrain\ExcelImports\Contracts\ExcelImportAnalyzesDataInterface;
use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum as Level;
use Gbrain\ExcelImports\Exceptions\ExcelImportNotPassedRulesValidation;
use Illuminate\Support\Collection;

class FlowImport implements ExcelImportAnalyzesDataInterface
{
    use Gbrain\ExcelImports\Concerns\AnalyzesData\AnalyzesData;

    /** @var array<int, array> */
    public array $handled = [];

    protected function getRulesCollection(): Collection
    {
        return collect([FlowRule::class]);
    }

    protected function handleRowImport(Collection $row): void
    {
        $this->handled[] = $row->toArray();
    }
}

class FlowRule extends Gbrain\ExcelImports\Abstracts\Analysis\ExcelImportAnalysisRule
{
    protected function getRowAnalysis(Collection $row, int $rowIndex): ?ExcelImportAnalysisResultDto
    {
        if (($row['fail'] ?? false) === true) {
            return new ExcelImportAnalysisResultDto(
                level: Level::ERROR,
                message: 'row failed',
                uniqueCode: 'FLOW_FAIL',
            );
        }

        return null;
    }
}

it('throws when analysis finds errors at or below threshold in analysis-only mode', function () {
    $import = new FlowImport();
    $import->withAnalysis(true)->minimalLevelToReport(Level::ERROR);

    $rows = collect([
        collect(['fail' => true]),
        collect(['fail' => false]),
    ]);

    expect(fn () => $import->collection($rows))
        ->toThrow(ExcelImportNotPassedRulesValidation::class);
});

it('bypasses analysis and imports when withoutAnalysis is used', function () {
    $import = new FlowImport();
    $import->withoutAnalysis();

    $rows = collect([
        collect(['fail' => true]),
        collect(['fail' => false]),
    ]);

    $import->collection($rows);

    expect($import->handled)->toHaveCount(2);
});

it('runs analysis then imports when withAnalysis(false) is used and no errors', function () {
    $import = new FlowImport();
    $import->withAnalysis(false)->minimalLevelToReport(Level::ERROR);

    $rows = collect([
        collect(['fail' => false]),
        collect(['fail' => false]),
    ]);

    $import->collection($rows);

    expect($import->handled)->toHaveCount(2);
});
