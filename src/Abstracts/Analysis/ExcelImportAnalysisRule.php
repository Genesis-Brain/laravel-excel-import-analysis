<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Abstracts\Analysis;

use Exception;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalysisRuleInterface;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalysisRulesRepositoryInterface;
use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum;
use Illuminate\Support\Collection;

/**
 * Base class for per-row analysis rules used during Excel import validation.
 *
 * Subclasses implement getRowAnalysis() to inspect a row and optionally return
 * an ExcelImportAnalysisResultDto describing the issue found. This base class
 * manages minimal reporting thresholds, repository association, and caching of
 * last check results per row index.
 */
abstract class ExcelImportAnalysisRule implements ExcelImportAnalysisRuleInterface
{
    /**
     * Minimal severity level that this rule will report.
     */
    private ExcelImportAnalysisLevelEnum $minimalReportLevel = ExcelImportAnalysisLevelEnum::INFO;

    /**
     * Repository where this rule is registered.
     */
    private ?ExcelImportAnalysisRulesRepositoryInterface $repository = null;

    /**
     * Last check results keyed by row index (0-based).
     *
     * @var Collection<int, ExcelImportAnalysisResultDto|null>
     */
    private Collection $checks;

    /**
     * Analyze a single row and return a result when an issue is detected.
     * Return null to indicate the row passed this rule.
     */
    abstract protected function getRowAnalysis(Collection $row, int $rowIndex): ?ExcelImportAnalysisResultDto;

    /**
     * {@inheritdoc}
     */
    final public function minimalLevelToReport(ExcelImportAnalysisLevelEnum $analysisLevelEnum): static
    {
        $this->minimalReportLevel = $analysisLevelEnum;

        return $this;
    }

    /**
     * Validate a row using this rule, enriching the result with context and
     * applying the minimal report level threshold.
     *
     * @throws Exception
     */
    final public function validateRow(Collection $row, int $rowIndex): ?ExcelImportAnalysisResultDto
    {
        $analysisResult = $this->getRowAnalysis($row, $rowIndex);

        if (! is_null($analysisResult)) {
            $analysisResult->excelImport = $this->getRepository()->getImportInstance();
            $analysisResult->rowIndex = $rowIndex;

            if ($analysisResult->level->lt($this->getMinimalReportLevel())) {
                $analysisResult = null;
            }
        }

        $this->saveCheckResult($rowIndex, $analysisResult);

        return $analysisResult;
    }

    /**
     * Get the last validation result for the given row index.
     * Returns false when the row has not been validated yet.
     */
    final public function getLastCheckResultForRow(int $rowIndex): false|null|ExcelImportAnalysisResultDto
    {
        if (! $this->checks->has($rowIndex)) {
            return false;
        }

        return $this->checks->get($rowIndex);
    }

    /**
     * Whether any recorded check result indicates an issue for any row.
     */
    final public function isError(): bool
    {
        return $this->checks->filter()->isNotEmpty();
    }

    /**
     * Whether there are no recorded issues across all validated rows.
     */
    final public function noErrors(): bool
    {
        return $this->checks->filter()->isEmpty();
    }

    /**
     * Get the repository this rule is bound to.
     */
    final public function getRepository(): ?ExcelImportAnalysisRulesRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Get the minimal severity level that this rule reports.
     */
    final public function getMinimalReportLevel(): ExcelImportAnalysisLevelEnum
    {
        return $this->minimalReportLevel;
    }

    /**
     * Bind the repository to this rule instance for context and configuration.
     */
    final public function withRepository(ExcelImportAnalysisRulesRepositoryInterface $repository): static
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Save the latest check result for a given row index.
     */
    private function saveCheckResult(int $rowIndex, ?ExcelImportAnalysisResultDto $result): void
    {
        if (! isset($this->checks)) {
            $this->checks = collect();
        }

        $this->checks->put($rowIndex, $result);
    }
}
