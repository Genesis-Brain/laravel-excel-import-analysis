<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Enums;

use Gbrain\ExcelImports\Attributes\Enums\ComparableValue;
use Gbrain\ExcelImports\Concerns\Enum\Comparable;

/**
 * Severity levels for analysis results produced during Excel import validation.
 *
 * The Comparable trait (via ComparableValue attributes) enables ordering:
 * INFO < WARNING < ERROR < CRITICAL.
 */
enum ExcelImportAnalysisLevelEnum: string
{
    use Comparable;

    /** Informational result that does not affect processing. */
    #[ComparableValue(0)]
    case INFO = 'info';

    /** Potential issue that should be reviewed but may not block processing. */
    #[ComparableValue(1)]
    case WARNING = 'warning';

    /** Problem that typically blocks processing of related data. */
    #[ComparableValue(2)]
    case ERROR = 'error';

    /** Critical failure requiring immediate attention; highest severity. */
    #[ComparableValue(3)]
    case CRITICAL = 'critical';
}
