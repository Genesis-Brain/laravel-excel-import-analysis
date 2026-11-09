<?php

declare(strict_types=1);

use Gbrain\ExcelImports\Abstracts\Analysis\ExampleRules\TitleIsRequiredImportRule;
use Gbrain\ExcelImports\Concerns\AnalyzesData\AnalyzesData;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalyzesDataInterface;
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum as Level;
use Gbrain\ExcelImports\Exceptions\ExcelImportNotPassedRulesValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IntegrationProductsImport implements ExcelImportAnalyzesDataInterface, WithHeadingRow
{
    use AnalyzesData;

    /** @var array<int, array<string, mixed>> */
    public array $handled = [];

    protected function getRulesCollection(): iterable
    {
        return [TitleIsRequiredImportRule::class];
    }

    protected function handleRowImport(Collection $row): void
    {
        $this->handled[] = $row->toArray();
    }
}

/**
 * Write a real .xlsx file to a temp path and return the path.
 *
 * @param  array<int, array<int, mixed>>  $rows  Rows including the heading row.
 */
function writeXlsx(array $rows): string
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

    $path = tempnam(sys_get_temp_dir(), 'import-').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

afterEach(function () {
    foreach (glob(sys_get_temp_dir().'/import-*.xlsx') ?: [] as $file) {
        @unlink($file);
    }
});

it('imports a real xlsx through the Laravel Excel reader using heading-row keys', function () {
    $path = writeXlsx([
        ['title', 'price'],
        ['Wine A', 10],
        ['Wine B', 20],
    ]);

    $import = (new IntegrationProductsImport)->minimalLevelToReport(Level::ERROR);

    Excel::import($import, $path);

    expect($import->handled)->toBe([
        ['title' => 'Wine A', 'price' => 10],
        ['title' => 'Wine B', 'price' => 20],
    ]);
});

it('reports the sheet row index of a failing row when read through the Laravel Excel reader', function () {
    $path = writeXlsx([
        ['title', 'price'],
        ['Wine A', 10],
        [null, 20],
    ]);

    $import = (new IntegrationProductsImport)->minimalLevelToReport(Level::ERROR);

    try {
        Excel::import($import, $path);
        $this->fail('Expected '.ExcelImportNotPassedRulesValidation::class.' to be thrown.');
    } catch (ExcelImportNotPassedRulesValidation $e) {
        $error = $e->firstError();

        expect($e->getAnalysisCollection())->toHaveCount(1)
            ->and($error->uniqueCode)->toBe('no-title')
            // Heading row is index 0, so the third sheet row has index 2.
            ->and($error->rowIndex)->toBe(2)
            ->and($error->cell)->toBe('A3')
            ->and($e->context())->toBe(['errors_by_code_on_rows' => ['no-title' => [3]]])
            ->and($import->handled)->toBe([]);
    }
});
