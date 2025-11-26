<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Concerns\AnalyzesData;

use Exception;
use Gbrain\ExcelImports\Abstracts\Analysis\ExcelImportAnalysisRulesRepository;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalysisRuleInterface;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalysisRulesRepositoryInterface;
use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum;
use Gbrain\ExcelImports\Exceptions\ExcelImportNotPassedRulesValidation;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

/**
 * Trait that orchestrates analysis-before-import for Excel imports.
 *
 * Provides a pipeline to validate rows using registered rules, optionally halt
 * on failures at or below a minimal report level, and then proceed with import
 * when configured to do so.
 */
trait AnalyzesData
{
    /**
     * Handle a single row import. Implemented by the consuming class.
     */
    abstract protected function handleRowImport(Collection $row): void;

    /**
     * Collected analysis results across validated rows.
     *
     * @var Collection<int, ExcelImportAnalysisResultDto>
     */
    protected Collection $analysis;

    /** Current row index during processing (0-based). */
    protected int $rowIndex = 0;

    /** Minimal level to report failures; rows below this are ignored. */
    private ExcelImportAnalysisLevelEnum $minimalReportLevel = ExcelImportAnalysisLevelEnum::INFO;

    /** Whether to run analysis before handling the import. */
    private bool $analyzeBeforeHandle = true;

    /** If true, only analysis runs and import is skipped. */
    private bool $onlyAnalysis = false;

    /** Lazily-initialized rules repository instance. */
    private ExcelImportAnalysisRulesRepositoryInterface $rulesRepository;

    /**
     * Entry point invoked by Maatwebsite\Excel: validate (and optionally import) rows.
     *
     * @throws BindingResolutionException
     * @throws Throwable
     */
    final public function collection(Collection $collection): void
    {
        $this->fillRulesRepository();
        $this->rowIndex = 0;

        if ($this instanceof WithHeadingRow) {
            $this->rowIndex++;
        }

        if ($this->analyzeBeforeHandle || $this->onlyAnalysis) {
            foreach ($collection as $row) {
                $this->analyzeRow($row);
                $this->rowIndex++;
            }

            if ($this->hasErrorsBelowOrEqualToReportLevel()) {
                throw new ExcelImportNotPassedRulesValidation($this->analysis);
            }

            if ($this->onlyAnalysis) {
                return;
            }

            $this->rowIndex = 0;
        }

        foreach ($collection as $row) {
            $this->handleRowImport($row);

            $this->rowIndex++;
        }
    }

    /**
     * Configure to skip analysis and proceed directly with import.
     */
    final public function withoutAnalysis(): static
    {
        $this->analyzeBeforeHandle = false;
        $this->onlyAnalysis = false;

        return $this;
    }

    /**
     * Enable analysis; set $withoutImport=true to run analysis only (no import).
     */
    final public function withAnalysis(bool $withoutImport = false): static
    {
        $this->analyzeBeforeHandle = true;
        $this->onlyAnalysis = $withoutImport;

        return $this;
    }

    /**
     * Set the minimal level to report when a rule is not passed.
     */
    final public function minimalLevelToReport(ExcelImportAnalysisLevelEnum $level): static
    {
        $this->minimalReportLevel = $level;

        return $this;
    }

    /**
     * Get the minimal severity level threshold for reporting.
     */
    final public function getMinimalReportLevel(): ExcelImportAnalysisLevelEnum
    {
        return $this->minimalReportLevel;
    }

    /**
     * Get/create the rules repository for this import.
     */
    final public function getRulesRepository(): ExcelImportAnalysisRulesRepositoryInterface
    {
        if (! isset($this->rulesRepository)) {
            $this->rulesRepository = (new ExcelImportAnalysisRulesRepository($this))
                ->minimalLevelToReport($this->minimalReportLevel);
        }

        return $this->rulesRepository;
    }

    /**
     * Resolve and register all rules into the repository.
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    protected function fillRulesRepository(): void
    {
        $repository = $this->getRulesRepository();
        $rules = $this->getRulesCollection();

        foreach ($rules as $rule) {
            if (is_string($rule)) {
                $rule = app()->make($rule);
            }

            if ($rule instanceof ExcelImportAnalysisRuleInterface) {
                $repository->add($rule);

                continue;
            }

            throw new Exception(sprintf('Class %s used as Excel Import Rule, but does not implement the ExcelImportAnalysisRuleInterface', $rule::class));
        }
    }

    /**
     * Provide the list of rule instances or FQCNs to be registered.
     *
     * @return list<ExcelImportAnalysisRuleInterface|class-string<ExcelImportAnalysisRuleInterface>>
     */
    protected function getRulesCollection(): iterable
    {
        return [];
    }

    /**
     * Analyze a single row against all registered rules and collect results.
     */
    final protected function analyzeRow(Collection $row): void
    {
        if (! isset($this->analysis)) {
            $this->analysis = collect();
        }

        foreach ($this->getRulesRepository()->rules() as $rule) {
            $result = $rule->validateRow($row, $this->rowIndex);

            if ($result instanceof ExcelImportAnalysisResultDto) {
                $this->analysis->push($result);
                $this->afterValidationFailed($result);
            }
        }
    }

    /**
     * Hook invoked immediately after a rule reports a failed validation for a row.
     *
     * Implementors may override to perform side effects such as logging,
     * aggregating metrics, early short-circuiting, or enriching context.
     * This method is called for every failure detected during analyzeRow().
     */
    protected function afterValidationFailed(ExcelImportAnalysisResultDto $result): void {}

    /**
     * Whether there are any results at or below the configured minimal level.
     */
    private function hasErrorsBelowOrEqualToReportLevel(): bool
    {
        return $this->analysis->filter(fn (ExcelImportAnalysisResultDto $dto) => $dto->level->lte($this->minimalReportLevel))->isNotEmpty();
    }
}
