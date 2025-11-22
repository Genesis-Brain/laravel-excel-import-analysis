<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Contracts;

use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Contract for import classes that participate in analysis/validation.
 *
 * Extends Maatwebsite\Excel\Concerns\ToCollection to receive rows as a
 * Collection and exposes access to the analysis rules repository.
 */
interface ExcelImportAnalyzesDataInterface extends ExcelImportHasMinimalReportLevelInterface, ToCollection
{
    /**
     * Get the rules repository associated with this import instance.
     */
    public function getRulesRepository(): ExcelImportAnalysisRulesRepositoryInterface;
}
