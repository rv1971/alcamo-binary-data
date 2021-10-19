<?php

namespace alcamo\binary_data;

use alcamo\exception\{OutOfRange, SyntaxError};

/**
 * @brief BCD-encoded data of any length
 *
 * Implemented as an uppercase hex-string so that single digits can be
 * accessed through the ArrayAccess interface and the number of digits
 * can be obtained via count().
 *
 * @attention May contain an odd number of digits.
 *
 * @date Last reviewed 2021-06-10
 */
class Bcd extends HexString
{
  /**
   * @brief Create from integer.
   *
   * @param $value int Value to encode.
   *
   * @param $minDigits int Minimum length of the result in digits.
   *
   * @param $allowOdd bool Whether the result may have an odd number of
   * digits. If `false` or `null`, the result my be left-padded with a '0'
   * digit.
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

        return new static(str_pad($value, $digits, '0', STR_PAD_LEFT));
    }

    /// Create from string made of decimal digits and whitespace
    public static function newFromString(string $text): HexString
    {
        $text = preg_replace('/\s+/', '', $text);

        if (strspn($text, '0123456789') != strlen($text)) {
            /** @throw alcamo::exception::SyntaxError if $text has content
             *  other than decimal digits and whitespace. */
            throw (new SyntaxError())->setMessageContext(
                [
                    'inData' => $text,
                    'atOffset' => strspn($text, '0123456789'),
                    'extraMessage' => 'not a valid integer literal'
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

    /// Get as integer, if possible
    public function toInt(): int
    {
        if (!isset($this->text_[0])) {
            return 0;
        }

        if (is_int($this->text_ + 0)) {
            return (int)$this->text_;
        }

        /** @throw alcamo::exception::OutOfRange if $content is too long to be
         *  represented as an integer. */
        throw (new OutOfRange())->setMessageContext(
            [
                'value' => $this,
                'lowerBound' => 0,
                'upperBound' => PHP_INT_MAX,
                'extraMessage' => 'unable to convert BCD to integer'
            ]
        );
    }

    /// Return new object left-padded with '0' to at least $minLength digits
    public function pad(?int $minLength = null, ?bool $allowOdd = null): self
    {
        if (!$allowOdd) {
            $minLength = max($minLength, count($this));

            if ($minLength & 1) {
                $minLength++;
            }
        }

        return new static(str_pad($this->text_, $minLength, '0', STR_PAD_LEFT));
    }
}
