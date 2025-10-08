<?php

namespace alcamo\binary_data;

use alcamo\exception\SyntaxError;

/**
 * @brief String consisting of an even number of hex digits
 *
 * @date Last reviewed 2025-10-08
 */
class EvenHexString extends HexString
{
    /// Create from hex string that may contain whitespace
    public static function newFromHexString(string $text): HexString
    {
        $text = strtoupper(preg_replace('/\s+/', '', $text));

        /** @throw alcamo::exception::SyntaxError if $text (without
         *  whitespace) does not have an even number of hexadecimal digits. */
        if (strlen($text) & 1) {
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $text,
                    'extraMessage' => 'not an even number of hex digits'
                ]
            );
        }

        return HexString::newFromHexString($text);
    }
}
