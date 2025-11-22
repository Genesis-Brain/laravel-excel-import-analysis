<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Exceptions;

use Exception;
use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;
use Illuminate\Support\Collection;

/**
 * Exception thrown when one or more analysis rules fail during Excel import.
 *
 * Carries the collection of ExcelImportAnalysisResultDto instances that caused
 * the failure and exposes a context() method suitable for structured logging.
 */
class ExcelImportNotPassedRulesValidation extends Exception
{
    /**
     * @param  Collection<int, ExcelImportAnalysisResultDto>  $analysisCollection  Collection of failures
     */
    public function __construct(
        private readonly Collection $analysisCollection,
    ) {
        parent::__construct(
            message: 'Some rules are not passed.',
        );
    }

    /**
     * Return a structured context array grouping row numbers by unique error code.
     *
     * Shape:
     * [
     *   'errors_by_code_on_rows' => array<string, list<int>> // 1-based row numbers per uniqueCode
     * ]
     */
    public function context(): array
    {
        $errorsGroupedByCode = $this->analysisCollection->groupBy('uniqueCode');

        return [
            'errors_by_code_on_rows' => $errorsGroupedByCode
                ->mapWithKeys(fn (Collection $analysisDtos, string $uniqueCode) => [
                    $uniqueCode => $analysisDtos->map(fn (ExcelImportAnalysisResultDto $analysisDto) => $analysisDto->rowIndex + 1),
                ])->toArray(),
        ];
    }

    /**
     * Returns the analysis collection with all the errors.
     */
    public function getAnalysisCollection(): Collection
    {
        return $this->analysisCollection;
    }

    /**
     * Get the first analysis result in the failure collection, if any.
     * Returns null when the collection is empty.
     */
    public function firstError(): ?ExcelImportAnalysisResultDto
    {
        return $this->analysisCollection->first();
    }
}
