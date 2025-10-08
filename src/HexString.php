<?php

namespace alcamo\binary_data;

use alcamo\string\StringObject;
use alcamo\exception\SyntaxError;

/**
 * @brief String with uppercase hex data content
 *
 * @attention May contain an odd number of digits. Use EvenHexString to
 * garantee an even number of digits.
 *
 * @date Last reviewed 2025-10-08
 */
class HexString extends StringObject
{
    public static function newFromBinaryString(string $data): self
    {
        return new static(strtoupper(bin2hex($data)));
    }

    /// Create from hex string that may contain whitespace
    public static function newFromHexString(string $text): self
    {
        $text = strtoupper(preg_replace('/\s+/', '', $text));

        if ($text != '' && !ctype_xdigit($text)) {
            /** @throw alcamo::exception::SyntaxError if $text has content
             *  other than hex digits and whitespace. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $text,
                    'extraMessage' => 'not a valid hex string'
                ]
            );
        }

        return new static($text);
    }

    protected $text_; ///< uppercase hexadecimal string

    /**
     * @brief Constructor is protected because it does not carry out any checks
     *
     * @attention $text must be a valid uppercase hex string.
     */
    protected function __construct(string $text)
    {
        parent::__construct($text);
    }

    /**
     * @brief Identical to alcamo::string::StringObject::offsetSet() except
     * that $value is checked for validity.
     */
    public function offsetSet($offset, $value)
    {
        $value = strtoupper($value);

        if (!ctype_xdigit($value) || strlen($value) > 1) {
            /** @throw alcamo::exception::SyntaxError if text is not exactly
             *  one hex digit. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $value,
                    'atOffset' => 0,
                    'extraMessage' => 'not a valid hex digit'
                ]
            );
        }

        parent::offsetSet($offset, $value);
    }

    public function toBinaryString(): BinaryString
    {
        return BinaryString::newFromHex($this->text_);
    }
}
