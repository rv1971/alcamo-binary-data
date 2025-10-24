<?php

namespace alcamo\binary_data;

use alcamo\exception\{OutOfRange, SyntaxError};

/**
 * @brief Class representing a compressed BCD.
 *
 * Implemented as an uppercase hex-string so that single digits can be
 * accessed through the ArrayAccess interface and the number of digits
 * can be obtained via count().
 *
 * @date Last reviewed 2025-10-09
 */
class CompressedBcd extends HexString
{
  /**
   * @brief Create from integer.
   *
   * @param $value int Value to encode.
   *
   * @param $minDigits int Minimum length of the result in digits. The result
   * is right-padded with 'F' digits as needed.
   *
   * @param $allowOdd bool Whether the result may have an odd number of
   * digits. If `false` or `null`, the result is right-padded with an 'F'
   * digit if necessary.
   */
    public static function newFromInt(
        int $value,
        ?int $minDigits = null,
        ?bool $allowOdd = null
    ): self {
        $digits = max(strlen($value), $minDigits);

        if (!$allowOdd) {
            $digits = ($digits + 1) & ~1;
        }

        return new static(str_pad($value, $digits, 'F', STR_PAD_RIGHT));
    }

    /// Create from numeric string that may contain whitespace.
    public static function newFromString(string $text): self
    {
        $text = strtoupper(preg_replace('/\s+/', '', $text));

        $bareContent = rtrim($text, 'F');

        if ($bareContent != '' && !ctype_digit($bareContent)) {
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $text,
                    'atOffset' => strspn($text, '0123456789'),
                    'extraMessage' => 'not a valid compressed BCD literal'
                ]
            );
        }

        return new static($text);
    }

    /**
     * @brief Constructor is protected because it does not carry out any checks
     *
     * @attention $text must be a valid BCD literal.
     */
    protected function __construct(string $text)
    {
        parent::__construct($text);
    }

    /**
     * @brief Return new object right-padded with 'F' to at least $minLength
     * digits
     *
     * @param $minDigits int Minimum length of the result in digits.
     *
     * @param $allowOdd bool Whether the result may have an odd number of
     * digits. If `false` or `null`, the result is left-padded with a '0'
     * digit if necessary.
     */
    public function pad(?int $minLength = null, ?bool $allowOdd = null): self
    {
        if (!$allowOdd) {
            $minLength = max($minLength, count($this));

            if ($minLength & 1) {
                $minLength++;
            }
        }

        return new static(str_pad($this->text_, $minLength, 'F'));
    }
}
