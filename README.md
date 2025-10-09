# Supplied classes

## Class `BinaryString`

Represents binary content, providing implicit conversion to
hexadecimal string, `count()` (returning the number of bytes) and `[]`
operations (accessing a single byte as its integer value).

Provides a number methods to convert from and to binary content and a
number of common operations such as trimming on either side, as well
as bitwise AND and OR operations.

## Class `HexString`

Represents an uppercase hexadecimal string, potentially with an odd
number of characters.

## Class `EvenHexString`

Represents an uppercase hexadecimal string with an even number of
characters.

## Class `Bcd`

Represents
[BCD](https://en.wikipedia.org/wiki/Binary-coded_decimal)-encoded
data.

Provides methods to convert from and to integers.

## Class `CompressedBcd`

Represents compressed numeric data as defined in the [EMV
specification](https://en.wikipedia.org/wiki/EMV).

The difference between `Bcd` and `CompressedBcd` is that in the
former, data is right-justified and may be padded with `0` nibbles on
the left, while in the latter, datav is left-justified and may be
padded with `F` nibbles on the right.
