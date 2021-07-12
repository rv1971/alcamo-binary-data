<?php

namespace alcamo\binary_data;

use alcamo\exception\{OutOfRange, Unsupported};

/**
 * @namespace alcamo::binary_data
 *
 * @brief Simple classes the model binary data, in particular BCD
 */

/**
 * @brief Array of bytes that represents binary content
 *
 * @date Last reviewed 2021-06-10
 */
class BinaryString implements \ArrayAccess, \Countable
{
    /**
     * @brief Create binary big endian representation
     *
     * @param $value integer to represent.
     *
     * @param $minBytes Minimum number of bytes to return. May be any positive
     * value. The result may be longer than this if necessary to represent the
     * value. By default, the minimum binary string needed to represent the
     * value is resturned; this may be any number, not necessarily a power of
     * two.
     */
    public static function newFromInt(int $value, int $minBytes = null): self
    {
        switch (true) {
            case $value <= 0xff:
                $result = chr($value);
                break;

            case $value <= 0xffff:
                $result = pack('n', $value);
                break;

            case $value <= 0xffffffff:
                $result = ltrim(pack('N', $value), "\x00");
                break;

            default:
                $result = ltrim(pack('J', $value), "\x00");
                break;
        }

        return new static(
            isset($minBytes)
            ? str_pad($result, $minBytes, "\00", STR_PAD_LEFT)
            : $result
        );
    }

    /// Create from hex string which may contain whitespace
    public static function newFromHex(string $hex): self
    {
        return new static(hex2bin(preg_replace('/\s+/', '', $hex)));
    }

    protected $data_; ///< Binary string

    /// Create from binary string
    public function __construct(string $data = null)
    {
        $this->data_ = (string)$data;
    }

    /// Return binary string as string
    public function getData(): string
    {
        return $this->data_;
    }

    /// Return representation as uppercase hex string
    public function __toString()
    {
        return strtoupper(bin2hex($this->data_));
    }

    /// Return number of bytes
    public function count()
    {
        return strlen($this->data_);
    }

    /// Whether a byte offset exists
    public function offsetExists($offset)
    {
        return isset($this->data_[$offset]);
    }

    /// Return the byte at $offset as an integer, *not as a character*
    public function offsetGet($offset)
    {
        return ord($this->data_[$offset]);
    }

    /// Set the byte at $offset from an integer, *not from a character*
    public function offsetSet($offset, $value)
    {
        if (!isset($this->data_[$offset])) {
            /** @throw alcamo::exception::OutOfRange if $offset outside of
             *  string */
            throw new OutOfRange(
                $offset,
                0,
                strlen($this->data_) - 1,
                '; offset outside of given binary string'
            );
        }

        $value = (int)$value;

        /** @throw alcamo::exception::OutOfRange if $value outside of [0,
         *  0xff]. */
        OutOfRange::throwIfOutside(
            $value,
            0,
            0xff,
            '; value does not represent a byte'
        );

        $this->data_[$offset] = chr($value);
    }

    /// Unsetting is not possible
    public function offsetUnset($offset)
    {
        /** @throw alcamo::exception::Unsupported at every invocation. */
        throw new Unsupported('Unsetting bytes in a binary string');
    }

    /* == operations == */

    /// Whether all bits are zero
    public function isZero(): bool
    {
        return strspn($this->data_, "\x00") == strlen($this->data_);
    }

    /// Return as integer, if possible
    public function toInt(): int
    {
        $data = ltrim($this->data_, "\x00");

        switch (strlen($data)) {
            case 1:
                return ord($data);

            case 2:
                return unpack('n', $data)[1];

            case 3:
                return unpack('N', "\x00$data")[1];

            case 4:
                return unpack('N', $data)[1];

            case 5:
                return unpack('J', "\x00\x00\x00$data")[1];

            case 6:
                return unpack('J', "\x00\x00$data")[1];

            case 7:
                return unpack('J', "\x00$data")[1];

            case 8:
                return unpack('J', $data)[1];

            default:
                /** @throw alcamo::exception::OutOfRange if $content is too
                 *  long to be represented as an integer. */
                OutOfRange::throwIfOutside(
                    strlen($data),
                    0,
                    8,
                    '; too long for conversion to integer'
                );
        }
    }

    /// Return new object without leading zero bytes
    public function ltrim(): self
    {
        return new self(ltrim($this->data_, "\x00"));
    }

    /// Return new object as bitwise AND of $this and $binString
    public function bitwiseAnd(self $binString): self
    {
        $length = max(count($this), count($binString));

        /** Left-pad with zeros as needed to obtain two strings of equal
         *  length. */
        $op1 = str_pad($this->data_, $length, "\x00", STR_PAD_LEFT);
        $op2 = str_pad($binString->data_, $length, "\x00", STR_PAD_LEFT);

        /** The result is as long as the longest operand. */
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= chr(ord($op1[$i]) & ord($op2[$i]));
        }

        return new self($result);
    }

    /// Return new object as bitwise OR of $this and $binString
    public function bitwiseOr(self $binString): self
    {
        $length = max(count($this), count($binString));

        /** Left-pad with zeros as needed to obtain two strings of equal
         *  length. */
        $op1 = str_pad($this->data_, $length, "\x00", STR_PAD_LEFT);
        $op2 = str_pad($binString->data_, $length, "\x00", STR_PAD_LEFT);

        /** The result is as long as the longest operand. */
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= chr(ord($op1[$i]) | ord($op2[$i]));
        }

        return new self($result);
    }
}
