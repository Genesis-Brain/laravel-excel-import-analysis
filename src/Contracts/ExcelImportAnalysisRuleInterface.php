<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Contracts;

use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;
use Illuminate\Support\Collection;

/**
 * Contract for per-row analysis rules executed during Excel import validation.
 *
 * Implementations should analyze a row and optionally emit a result. They are
 * expected to keep track of the last result per row and expose utility methods
 * to query overall error state.
 */
interface ExcelImportAnalysisRuleInterface extends ExcelImportHasMinimalReportLevelInterface
{
    /**
     * Validate a given row and optionally return an analysis result.
     * Returning null indicates the row passed this rule.
     */
    public function validateRow(Collection $row, int $rowIndex): ?ExcelImportAnalysisResultDto;

    /**
     * Get the last validation result for the given row index.
     *
     * Returns false if the row has never been checked, null if it was checked
     * and no issue was found, or the last ExcelImportAnalysisResultDto when an
     * issue was found.
     *
     * @param  int  $rowIndex  Zero-based row index
     */
    public function getLastCheckResultForRow(int $rowIndex): false|null|ExcelImportAnalysisResultDto;

    /**
     * Whether any issue has been recorded across all checked rows.
     */
    public function isError(): bool;

    /**
     * Whether no issues have been recorded so far.
     */
    public function noErrors(): bool;

    /**
     * Return the repository that contains this rule instance.
     */
    public function getRepository(): ?ExcelImportAnalysisRulesRepositoryInterface;

    /**
     * Bind the repository to this rule and return the same instance (fluent).
     */
    public function withRepository(ExcelImportAnalysisRulesRepositoryInterface $repository): static;
}
