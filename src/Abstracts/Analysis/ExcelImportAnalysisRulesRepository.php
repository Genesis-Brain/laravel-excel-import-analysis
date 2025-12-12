<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Abstracts\Analysis;

use Gbrain\ExcelImports\Contracts\ExcelImportAnalysisRuleInterface;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalysisRulesRepositoryInterface;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalyzesDataInterface;
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum;
use Illuminate\Support\Collection;

/**
 * Default repository implementation for registering and retrieving analysis rules
 * used during Excel import validation.
 *
 * The repository also carries a minimal report level that rules can consult to
 * decide whether to emit results or suppress them below a threshold.
 */
class ExcelImportAnalysisRulesRepository implements ExcelImportAnalysisRulesRepositoryInterface
{
    /**
     * Minimal level of the result to be persisted and reported.
     */
    private ExcelImportAnalysisLevelEnum $minimalReportLevel = ExcelImportAnalysisLevelEnum::INFO;

    /**
     * Registered rules keyed by their FQCN.
     *
     * @var Collection<class-string<ExcelImportAnalysisRuleInterface>, ExcelImportAnalysisRuleInterface>
     */
    private Collection $rules;

    /**
     * @param  ExcelImportAnalyzesDataInterface  $importInstance  The import instance associated to these rules
     */
    public function __construct(private readonly ExcelImportAnalyzesDataInterface $importInstance)
    {
        $this->rules = collect();
    }

    /**
     * Register a rule instance in the repository and propagate configuration.
     * If no collection exists yet, it gets initialized lazily.
     */
    final public function add(ExcelImportAnalysisRuleInterface $analysisRule, ?ExcelImportAnalysisLevelEnum $minimalReportLevel = null): void
    {
        $this->rules->put(
            $analysisRule::class,
            $analysisRule
                ->withRepository($this)
                ->minimalLevelToReport($minimalReportLevel ?? $this->getMinimalReportLevel())
        );
    }

    /**
     * Retrieve a previously registered rule by its FQCN.
     */
    final public function find(string $analysisRuleClassName, ?ExcelImportAnalysisRuleInterface $default = null): ?ExcelImportAnalysisRuleInterface
    {
        return $this->rules->get($analysisRuleClassName);
    }

    /**
     * Return the collection of registered rules.
     *
     * @return Collection<class-string<ExcelImportAnalysisRuleInterface>, ExcelImportAnalysisRuleInterface>
     */
    final public function rules(): Collection
    {
        return $this->rules;
    }

    /**
     * Set the minimal severity level that should be reported by rules.
     */
    final public function minimalLevelToReport(ExcelImportAnalysisLevelEnum $analysisLevelEnum): static
    {
        $this->minimalReportLevel = $analysisLevelEnum;

        return $this;
    }

    /**
     * Get the current minimal report level threshold.
     */
    final public function getMinimalReportLevel(): ExcelImportAnalysisLevelEnum
    {
        return $this->minimalReportLevel;
    }

    /**
     * Return the import instance associated to this repository.
     */
    final public function getImportInstance(): ExcelImportAnalyzesDataInterface
    {
        return $this->importInstance;
    }
}
