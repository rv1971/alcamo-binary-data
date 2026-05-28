<?php

namespace alcamo\binary_data;

use alcamo\exception\ReadonlyViolation;

/**
 * @brief Provide writing methods of the ArrayAccess interface that always
 * throw
 *
 * Together with `ReadArrayAccessTrait` or any other implementation of the
 * reading methods of the `ArrayAccess` interface, this can be used to build
 * a class that supports array access in a readonly way.
 *
 * @sa [ArrayAccess interface](https://www.php.net/manual/en/class.arrayaccess)
 *
 * @date Last reviewed 2025-10-13
 */
trait PreventWriteArrayAccessTrait
{
    public function offsetSet($offset, $value): void
    {
        /** @throw alcamo::exception::ReadonlyViolation in every
         *  invocation. */
        throw new ReadonlyViolation();
    }

    public function offsetUnset($offset): void
    {
        /** @throw alcamo::exception::ReadonlyViolation in every
         *  invocation. */
        throw new ReadonlyViolation();
    }
}
