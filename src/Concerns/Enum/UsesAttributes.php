<?php

declare(strict_types=1);

namespace Gbrain\ExcelImports\Concerns\Enum;

use Attribute;
use Exception;
use Illuminate\Support\Str;
use ReflectionEnumUnitCase;

/**
 * Trait offering helpers to read PHP8 attributes declared on enum unit cases.
 *
 * Intended to be used inside enum cases (unit-style), leveraging reflection to
 * obtain attribute constructor arguments for convenience.
 */
trait UsesAttributes
{
    /**
     * Get the first matching attribute value for the current enum case.
     *
     * The returned value is the first constructor argument of the attribute.
     * If the attribute is not found and optional is true, returns $valueOnNotFound;
     * otherwise throws an Exception.
     *
     * @template AttributeClass of Attribute
     *
     * @param  class-string<AttributeClass>  $attributeClass  Fully-qualified attribute class name
     * @param  bool  $optional  If true, return $valueOnNotFound when not found; otherwise throw
     * @param  mixed  $valueOnNotFound  Value to return when optional and not found (defaults to null)
     *
     * @return AttributeClass The first constructor argument value of the attribute
     *
     * @throws Exception When attribute is not found and optional=false
     */
    final public function getAttribute(string $attributeClass, bool $optional = false, mixed $valueOnNotFound = null): mixed
    {
        $enumReflection = new ReflectionEnumUnitCase($this::class, $this->name);
        $enumAttribute = $enumReflection->getAttributes($attributeClass);

        if (empty($enumAttribute)) {

            if ($optional) {
                return $valueOnNotFound;
            }

            throw new Exception(Str::afterLast($attributeClass, '\\')." not found for case '".$this->name."' in enum '".$this::class."'.");
        }

        return $enumAttribute[0]->newInstance();
    }

    /**
     * Return an associative array of all attributes defined on the current case.
     * The array is keyed by short attribute class name and the value is the first
     * constructor argument of the attribute.
     *
     * @return array<string, mixed>
     */
    final public function getAttributes(): array
    {
        $enumAttributes = [];
        $enumReflection = new ReflectionEnumUnitCase($this::class, $this->name);

        foreach ($enumReflection->getAttributes() as $enumAttribute) {
            $enumAttributes[Str::afterLast($enumAttribute->getName(), '\\')] = $enumAttribute->getArguments()[0];
        }

        return $enumAttributes;
    }
}
