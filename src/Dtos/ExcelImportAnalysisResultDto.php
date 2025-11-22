<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Dtos;

use Gbrain\ExcelImports\Contracts\ExcelImportAnalyzesDataInterface;
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum;

/**
 * Represents a single analysis outcome generated during Excel import validation.
 *
 * Instances hold a severity level, a human-readable message, a machine-readable
 * code, and optional positional/context information (cell, row, column, etc.).
 * Fluent helpers allow composing messages/codes without altering external API.
 */
class ExcelImportAnalysisResultDto
{
    /**
     * Divider used to concatenate message fragments (extendMessage()).
     * Default: "; " (semicolon + space).
     */
    private string $messageDivider = '; ';

    /**
     * Divider used to concatenate code fragments (extendCode()).
     * Default: "_" (underscore).
     */
    private string $codeDivider = '_';

    /**
     * @param  ExcelImportAnalysisLevelEnum  $level  Severity level of the result (INFO, WARNING, ERROR, CRITICAL)
     * @param  string  $message  Human-readable description
     * @param  string  $uniqueCode  Machine-readable identifier (stable for programmatic checks)
     * @param  ExcelImportAnalyzesDataInterface|null  $excelImport  Import instance that produced this result (optional)
     * @param  string|null  $cell  A1-style cell coordinate (e.g., "B12") when applicable
     * @param  int|null  $rowIndex  Zero- or one-based row index depending on producer convention
     * @param  string|null  $columnName  Column header/name if available
     * @param  array<string,mixed>  $context  Extra metadata useful for templating/logging/telemetry
     */
    public function __construct(
        public ExcelImportAnalysisLevelEnum $level,
        public string $message,
        public string $uniqueCode,
        public ?ExcelImportAnalyzesDataInterface $excelImport = null,
        public ?string $cell = null,
        public ?int $rowIndex = null,
        public ?string $columnName = null,
        public array $context = [],
    ) {}

    /**
     * Append a message fragment using the current message divider.
     * Example: "Missing SKU; Invalid price".
     */
    public function appendMessage(string $additionalMessage): self
    {
        $arrayMessage = explode($this->messageDivider, $this->message);
        $this->message = implode($this->messageDivider, [...$arrayMessage, $additionalMessage]);

        return $this;
    }

    /**
     * Append a code fragment using the current code divider.
     * Example: "SKU_REQUIRED_INVALID_PRICE".
     */
    public function appendCode(string $additionalCode): self
    {
        $arrayCode = explode($this->codeDivider, $this->uniqueCode);
        $this->uniqueCode = implode($this->codeDivider, [...$arrayCode, $additionalCode]);

        return $this;
    }

    /**
     * Set the code divider used by extendCode().
     */
    public function withCodeDivider(string $divider): self
    {
        $this->codeDivider = $divider;

        return $this;
    }

    /**
     * Set the message divider used by extendMessage().
     */
    public function withMessageDivider(string $divider): self
    {
        $this->messageDivider = $divider;

        return $this;
    }

    /**
     * Factory: create an INFO-level result.
     *
     * @param  array<string,mixed>  $context
     */
    public static function info(
        string $message,
        string $uniqueCode,
        ?string $cell = null,
        ?string $columnName = null,
        ?array $context = [],
    ): self {
        return new self(
            level: ExcelImportAnalysisLevelEnum::INFO,
            message: $message,
            uniqueCode: $uniqueCode,
            cell: $cell,
            columnName: $columnName,
            context: $context,
        );
    }

    /**
     * Factory: create a WARNING-level result.
     *
     * @param  array<string,mixed>  $context
     */
    public static function warning(
        string $message,
        string $uniqueCode,
        ?string $cell = null,
        ?string $columnName = null,
        ?array $context = [],
    ): self {
        return new self(
            level: ExcelImportAnalysisLevelEnum::WARNING,
            message: $message,
            uniqueCode: $uniqueCode,
            cell: $cell,
            columnName: $columnName,
            context: $context,
        );
    }

    /**
     * Factory: create an ERROR-level result.
     *
     * @param  array<string,mixed>  $context
     */
    public static function error(
        string $message,
        string $uniqueCode,
        ?string $cell = null,
        ?string $columnName = null,
        ?array $context = [],
    ): self {
        return new self(
            level: ExcelImportAnalysisLevelEnum::ERROR,
            message: $message,
            uniqueCode: $uniqueCode,
            cell: $cell,
            columnName: $columnName,
            context: $context,
        );
    }

    /**
     * Factory: create a CRITICAL-level result.
     *
     * @param  array<string,mixed>  $context
     */
    public static function critical(
        string $message,
        string $uniqueCode,
        ?string $cell = null,
        ?string $columnName = null,
        ?array $context = [],
    ): self {
        return new self(
            level: ExcelImportAnalysisLevelEnum::CRITICAL,
            message: $message,
            uniqueCode: $uniqueCode,
            cell: $cell,
            columnName: $columnName,
            context: $context,
        );
    }
}
