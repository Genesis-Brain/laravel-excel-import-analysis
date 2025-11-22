<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Attributes\Enums;

use Attribute;

/**
 * Attribute to decorate enum cases (class constants) with a custom comparable value.
 *
 * This allows mapping a case to an integer or string that should be used for
 * comparisons/sorting instead of the native case name/value.
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class ComparableValue
{
    /**
     * @param  int|string  $comparableValue  Value used when comparing/sorting the enum case
     */
    public function __construct(
        public int|string $comparableValue,
    ) {}
}
