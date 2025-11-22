<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Contracts;

use Illuminate\Support\Collection;

/**
 * Contract for repositories that register and retrieve analysis rules for a
 * given Excel import execution.
 */
interface ExcelImportAnalysisRulesRepositoryInterface extends ExcelImportHasMinimalReportLevelInterface
{
    /**
     * Register a rule instance in the repository.
     */
    public function add(ExcelImportAnalysisRuleInterface $analysisRule): void;

    /**
     * Find a rule by its fully-qualified class name.
     *
     * @param  class-string<ExcelImportAnalysisRuleInterface>  $analysisRuleClassName
     */
    public function find(string $analysisRuleClassName, ?ExcelImportAnalysisRuleInterface $default = null): ?ExcelImportAnalysisRuleInterface;

    /**
     * Return all registered rule instances.
     *
     * @return Collection<int, ExcelImportAnalysisRuleInterface>
     */
    public function rules(): Collection;

    /**
     * Get the import instance associated with this repository.
     */
    public function getImportInstance(): ?ExcelImportAnalyzesDataInterface;
}
