# Gbrain Excel Imports

A reusable, rule-based Excel import analysis framework for Laravel.  
It provides structured per-row analysis, severity levels, contextual DTOs, and a clean import architecture that integrates seamlessly with **Laravel Excel**.

This package is designed to help you build **robust, extensible, and maintainable Excel import pipelines** in your Laravel applications.

## ✨ Features

- 🧩 Rule-based per-row validation
- ⚠️ Built-in severity levels (`INFO`, `WARNING`, `ERROR`, `CRITICAL`)
- 📄 Rich DTOs (messages, codes, row/column/cell, context)
- 🏗 Pluggable rules via repository
- ♻️ Reusable import architecture using traits & contracts
- 📚 Clean interfaces and abstract base classes
- 🔌 Fully compatible with [Maatwebsite/Laravel-Excel](https://laravel-excel.com)

## 📦 Installation

```bash
composer require gbrain/excel-imports
```

Requires:

- PHP >= 8.2
- Laravel >= 10
- maatwebsite/excel >= 3.1

## 🚀 Basic Usage

### 1. Create an Import

```php
use Gbrain\ExcelImports\Concerns\AnalyzesData\AnalyzesData;
use Gbrain\ExcelImports\Contracts\ExcelImportAnalyzesDataInterface;
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ExcelImportAnalyzesDataInterface, WithHeadingRow
{
    use AnalyzesData;

    /**
     * Business logic that runs once a row has passed validation.
     */
    protected function handleRowImport(Collection $row): void
    {
        // Persist the row, dispatch a job, etc.
    }

    /**
     * Register the analysis rules for this import.
     *
     * @return list<class-string<\Gbrain\ExcelImports\Contracts\ExcelImportAnalysisRuleInterface>>
     */
    public function getRulesCollection(): iterable
    {
        return [
            \Gbrain\ExcelImports\Abstracts\Analysis\ExampleRules\TitleIsRequiredImportRule::class,
            // Your custom rules...
        ];
    }

    /**
     * Minimal analysis level that should be considered an error for this import.
     */
    public function getMinimalReportLevel(): ExcelImportAnalysisLevelEnum
    {
        return ExcelImportAnalysisLevelEnum::WARNING;
    }
}
```

### 2. Run the Import

```php
use Maatwebsite\Excel\Facades\Excel;

Excel::import(new ProductsImport(), 'products.xlsx');
```

If any analysis results are produced at or below the configured minimal level, an
`Gbrain\ExcelImports\Exceptions\ExcelImportNotPassedRulesValidation` exception
will be thrown. You can catch it to surface errors to users or logs.

```php
try {
    Excel::import(new ProductsImport(), 'products.xlsx');
} catch (\Gbrain\ExcelImports\Exceptions\ExcelImportNotPassedRulesValidation $e) {
    foreach ($e->getAnalysisCollection() as $result) {
        // $result is an ExcelImportAnalysisResultDto
        // e.g. $result->message, $result->uniqueCode, $result->rowIndex, etc.
    }
}
```

## 🔍 Controlling Analysis Execution: `withAnalysis()` and `withoutAnalysis()`

The `AnalyzesData` trait provides two important methods to control **when analysis rules run** during an import:

### ### ✅ `withAnalysis(bool $withoutImport = false)` (default behaviour)
Enables analysis and allows you to decide whether the import should also run.

Since `$onlyAnalysis` is **false by default**, calling `withAnalysis()` without arguments means:

➡ **Analysis runs**
➡ **Import also runs (if analysis passes)**

You may optionally pass a boolean:
```php
$import->withAnalysis(false); // analysis + import
$import->withAnalysis(true);  // analysis only (no import)
```

- `withAnalysis(false)` → normal mode: validate rows *and* import if valid
- `withAnalysis(true)`  → analysis-only mode: validate rows, skip import entirely

Example (normal mode):
```php
Excel::import((new ProductsImport())->withAnalysis(), 'products.xlsx'); // default state
```
- Each row is validated using your registered rules
- All analysis results are collected
- If any rule produces a result at or below the minimal report level, an exception is thrown

### ### ⛔ `withoutAnalysis()`
Disables all rule checks.

Use this when you want to:
- Import data **without validation**
- Re-run an import using only the business logic (`handleRowImport()`)
- Bypass rules temporarily (e.g. admin override)

Example:
```php
Excel::import((new ProductsImport())->withoutAnalysis(), 'products.xlsx');
```

### ### 🔄 How it works internally
`withAnalysis()` and `withoutAnalysis()` work together using two internal flags:

- `$analyzeBeforeHandle` — whether rules should run at all
- `$onlyAnalysis` — whether **only** rules should run (skipping import)

According to the current implementation:

### ✔ Default state
- `$analyzeBeforeHandle = true`
- `$onlyAnalysis = false`

So calling `withAnalysis()` with **no arguments**:
- enables analysis
- **does NOT** skip import

### ✔ Truth table
| Method call              | `$analyzeBeforeHandle`   | `$onlyAnalysis` | Effect                                |
|--------------------------|--------------------------|-----------------|---------------------------------------|
| `withAnalysis()`         | `true`                   | `false`         | Run analysis **and then import**      |
| `withAnalysis(false)`    | `true`                   | `false`         | Same as above                         |
| `withAnalysis(true)`     | `true`                   | `true`          | Run analysis **only**, no import      |
| `withoutAnalysis()`      | `false`                  | `false`         | Skip analysis, run import only        |

### ✔ What actually happens
- If **analysis is enabled** (`$analyzeBeforeHandle = true`), rules run on each row.
- If **minimal report level is breached**, an exception is thrown.
- If **only analysis** is enabled (`$onlyAnalysis = true`), the import is **skipped entirely**.
- If **withoutAnalysis()** is called, rules are skipped and only import logic runs.

### Summary
- **withAnalysis()** → analysis + import (default)
- **withAnalysis(true)** → analysis only
- **withAnalysis(false)** → analysis + import
- **withoutAnalysis()** → import only

This gives you full control over whether to run validation-only scans, full validated imports, or unvalidated imports.**, without changing your import class.

## 🧪 Writing Custom Rules

Rules extend the abstract `ExcelImportAnalysisRule` and return an
`ExcelImportAnalysisResultDto` when the rule fails.

```php
use Gbrain\ExcelImports\Abstracts\Analysis\ExcelImportAnalysisRule;
use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;
use Illuminate\Support\Collection;

class PositiveQuantityRule extends ExcelImportAnalysisRule
{
    protected function getRowAnalysis(Collection $row, int $rowIndex): ?ExcelImportAnalysisResultDto
    {
        $qty = (int) ($row->get('quantity') ?? 0);

        if ($qty <= 0) {
            return ExcelImportAnalysisResultDto::error(
                message: 'Quantity must be positive',
                uniqueCode: 'qty-negative',
                cell: 'B'.($rowIndex + 1),
                columnName: 'Quantity',
                context: ['value' => $qty],
            );
        }

        return null;
    }
}
```

Then register it in your import’s `getRulesCollection()` method.

## 📊 Severity Levels

The package ships with a built-in severity enum:

```php
use Gbrain\ExcelImports\Enums\ExcelImportAnalysisLevelEnum;

ExcelImportAnalysisLevelEnum::INFO;
ExcelImportAnalysisLevelEnum::WARNING;
ExcelImportAnalysisLevelEnum::ERROR;
ExcelImportAnalysisLevelEnum::CRITICAL;
```

Levels are comparable using helper methods:

```php
$level->gte(ExcelImportAnalysisLevelEnum::WARNING);
$level->lt(ExcelImportAnalysisLevelEnum::ERROR);
```

Internally, comparison is driven by PHP 8 attributes (`ComparableValue`) attached
to each enum case.

## 🧱 Architecture Overview

- **Contracts** — describe import, rule, and repository responsibilities
- **Abstracts** — provide base behavior (rules, repository)
- **Concerns** — traits that orchestrate analysis (`AnalyzesData`)
- **Enums** — severity levels, with comparable semantics
- **DTOs** — carry structured analysis results
- **Exceptions** — encapsulate failed analysis and structured context

## 🧪 Testing

This package is designed to be test-friendly. Example Pest tests:

```php
use Gbrain\ExcelImports\Abstracts\Analysis\ExampleRules\TitleIsRequiredImportRule;
use Gbrain\ExcelImports\Dtos\ExcelImportAnalysisResultDto;

test('TitleIsRequiredImportRule flags missing title', function () {
    $rule = new TitleIsRequiredImportRule();

    $result = $rule->validateRow(collect(['title' => null]), 0);

    expect($result)->toBeInstanceOf(ExcelImportAnalysisResultDto::class)
        ->and($result->uniqueCode)->toBe('no-title')
        ->and($result->level->name)->toBe('CRITICAL');
});
```

Run tests with:

```bash
vendor/bin/pest
```

## 📄 License

MIT
