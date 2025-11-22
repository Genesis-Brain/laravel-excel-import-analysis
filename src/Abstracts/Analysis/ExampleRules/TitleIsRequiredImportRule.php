<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Abstracts\Analysis\ExampleRules;

use Gbrain\ExcelImports\Abstracts\Analysis\ExcelImportAnalysisRule;
use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;
use Illuminate\Support\Collection;

class TitleIsRequiredImportRule extends ExcelImportAnalysisRule
{
    protected function getRowAnalysis(Collection $row, int $rowIndex): ?ExcelImportAnalysisResultDto
    {
        if (! $row->get('title')) {
            return ExcelImportAnalysisResultDto::critical(
                message: 'Column title not found',
                uniqueCode: 'no-title',
                cell: 'A'.($rowIndex + 1),
                columnName: 'Title',
            );
        }

        return null;
    }
}
