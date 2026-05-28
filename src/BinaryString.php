<?php

namespace alcamo\binary_data;

use alcamo\exception\{OutOfRange, Unsupported};

/**
 * @brief Mutable binary content
 *
 * @date Last reviewed 2025-10-08
 */
class BinaryString extends AbstractBinaryString
{
    /// Set the byte at $offset from an integer, *not from a character*
    public function offsetSet($offset, $value)
    {
        if (!isset($this->data_[$offset])) {
            /** @throw alcamo::exception::OutOfRange if $offset outside of
             *  string */
            throw (new OutOfRange())->setMessageContext(
                [
                    'value' => $offset,
                    'lowerBound' => 0,
                    'upperBound' => strlen($this->data_) - 1,
                    'extraMessage' => 'offset outside of given binary string'
                ]
            );
        }

        $value = (int)$value;

        /** @throw alcamo::exception::OutOfRange if $value outside of [0,
         *  0xff]. */
        OutOfRange::throwIfOutside(
            $value,
            0,
            0xff,
            [ 'extraMessage' => 'value does not represent a byte' ]
        );

        $this->data_[$offset] = chr($value);
    }

    /// Unsetting is not possible
    public function offsetUnset($offset)
    {
        /** @throw alcamo::exception::Unsupported at every invocation. */
        throw (new Unsupported())->setMessageContext(
            [
                'feature' => 'Unsetting bytes in a binary string',
                'inData' => (string)$this,
                'atOffset' => $offset
            ]
        );
    }
}
