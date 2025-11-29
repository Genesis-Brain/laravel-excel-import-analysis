<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Concerns\Enum;

use Exception;
use Gbrain\ExcelImports\Attributes\Enums\ComparableValue;

/**
 * Trait providing rich comparison operations for enum-like classes based on
 * a ComparableValue attribute attached to each case.
 *
 * Requires the consumer (backed enum or enum-like class) to use UsesAttributes
 * and to have a ComparableValue attribute defined for every comparable case.
 */
trait Comparable
{
    use UsesAttributes;

    /**
     * Return the normalized comparable form for this case, sourced from the
     * ComparableValue attribute.
     *
     * @throws Exception When the ComparableValue attribute is missing
     */
    protected function comparableForm(): int|float|string
    {
        $attribute = $this->getAttribute(ComparableValue::class);

        return $attribute->comparableValue;
    }

    /**
     * Whether this case is strictly less than the other.
     *
     * @throws Exception
     */
    final public function isLessThan(self $other): bool
    {
        return $this->comparableForm() < $other->comparableForm();
    }

    /**
     * Whether this case is less than or equal to the other.
     *
     * @throws Exception
     */
    final public function isLessOrEquals(self $other): bool
    {
        return $this->comparableForm() <= $other->comparableForm();
    }

    /**
     * Whether this case is strictly greater than the other.
     *
     * @throws Exception
     */
    final public function isGreater(self $other): bool
    {
        return $this->comparableForm() > $other->comparableForm();
    }

    /**
     * Whether this case is greater than or equal to the other.
     *
     * @throws Exception
     */
    final public function isGreaterOrEquals(self $other): bool
    {
        return $this->comparableForm() >= $other->comparableForm();
    }

    /**
     * Strict equality based on the comparable form.
     *
     * @throws Exception
     */
    final public function equals(self $other): bool
    {
        return $this->comparableForm() === $other->comparableForm();
    }

    /**
     * Negation of equals().
     *
     * @throws Exception
     */
    final public function notEquals(self $other): bool
    {
        return ! $this->equals($other);
    }

    /**
     * Whether this case lies inclusively between the min and max cases.
     *
     * @throws Exception
     */
    final public function between(self $min, self $max): bool
    {
        return $this->comparableForm() >= $min->comparableForm()
            && $this->comparableForm() <= $max->comparableForm();
    }

    /**
     * Alias for isLessThan().
     *
     * @throws Exception
     */
    final public function lt(self $other): bool
    {
        return $this->isLessThan($other);
    }

    /**
     * Alias for isLessOrEquals().
     *
     * @throws Exception
     */
    final public function lte(self $other): bool
    {
        return $this->isLessOrEquals($other);
    }

    /**
     * Alias for isGreater().
     *
     * @throws Exception
     */
    final public function gt(self $other): bool
    {
        return $this->isGreater($other);
    }

    /**
     * Alias for isGreaterOrEquals().
     *
     * @throws Exception
     */
    final public function gte(self $other): bool
    {
        return $this->isGreaterOrEquals($other);
    }

    /**
     * Compare two cases using PHP's spaceship operator semantics.
     * Returns -1, 0, or 1.
     *
     * @throws Exception
     */
    final public function compareTo(self $other): int
    {
        return $this->comparableForm() <=> $other->comparableForm();
    }
}
