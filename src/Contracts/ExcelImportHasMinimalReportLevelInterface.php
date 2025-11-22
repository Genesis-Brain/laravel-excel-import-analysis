<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Contracts;

use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum;

/**
 * Contract for components that expose and honor a minimal report level
 * threshold for analysis results.
 */
interface ExcelImportHasMinimalReportLevelInterface
{
    /**
     * Get the current minimal severity level that should be reported.
     */
    public function getMinimalReportLevel(): ExcelImportAnalysisLevelEnum;

    /**
     * Set the minimal severity level to report; results below this threshold
     * should be ignored by implementers.
     */
    public function minimalLevelToReport(ExcelImportAnalysisLevelEnum $analysisLevelEnum): static;
}
