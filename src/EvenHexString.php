<?php

namespace alcamo\binary_data;

use alcamo\exception\SyntaxError;

/**
 * @brief String consisting of an even number of hex digits
 *
 * @date Last reviewed 2021-06-10
 */
class EvenHexString extends HexString
{
    /// Create from hex string that may contain whitespace
    public static function newFromString(string $text): HexString
    {
        $text = strtoupper(preg_replace('/\s+/', '', $text));

        /** @throw alcamo::exception::SyntaxError if $text does not have an
         *  even number of digits. */
        if (strlen($text) & 1) {
            throw new SyntaxError(
                $text,
                0,
                '; not an even number of hex digits'
            );
        }

        return new self($text);
    }
}
