<?php

namespace alcamo\binary_data;

use PHPUnit\Framework\TestCase;
use alcamo\exception\{LengthOutOfRange, OutOfRange, Unsupported};
use Ds\Set;

class BinaryStringTest extends TestCase
{
    /**
     * @dataProvider newFromIntProvider
     */
    public function testNewFromInt(
        $value,
        $minBytes,
        $expectedGetData,
        $expectedToString,
        $expectedCount,
        $expectedIsZero,
        $expectedLtrim
    ): void {
        $binaryString = BinaryString::newFromInt($value, $minBytes);

        $this->assertSame($expectedToString, (string)$binaryString);

        $this->assertSame($expectedGetData, $binaryString->getData());

        $this->assertSame($expectedCount, count($binaryString));

        $this->assertSame($expectedIsZero, $binaryString->isZero());

        $this->assertSame($expectedLtrim, (string)$binaryString->ltrim());

        if ($value >= 0) {
            $this->assertSame($value, $binaryString->toInt());
        } else {
            $this->assertSame($value, $binaryString->toInt(true));
        }
    }

    public function newFromIntProvider(): array
    {
        return [
            '0-null' => [
                0,
                null,
                "\x00",
                "00",
                1,
                true,
                ''
            ],
            '0-3' => [
                0,
                3,
                "\x00\x00\x00",
                "000000",
                3,
                true,
                ''
            ],
            '0-20' => [
                0,
                20,
                "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
                "0000000000000000000000000000000000000000",
                20,
                true,
                ''
            ],
            '255-null' => [
                255,
                null,
                "\xff",
                "FF",
                1,
                false,
                "FF"
            ],
            '256-null' => [
                256,
                null,
                "\x01\x00",
                "0100",
                2,
                false,
                "0100"
            ],
            '0x1234-5' => [
                0x1234,
                5,
                "\x00\x00\x00\x12\x34",
                "0000001234",
                5,
                false,
                "1234"
            ],
            '0x123456-null' => [
                0x123456,
                null,
                "\x12\x34\x56",
                "123456",
                3,
                false,
                "123456"
            ],
            '0x123456789-null' => [
                0x123456789,
                null,
                "\x01\x23\x45\x67\x89",
                "0123456789",
                5,
                false,
                "0123456789"
            ],
            '-3-null' => [
                -3,
                null,
                "\xFD",
                "FD",
                1,
                false,
                "FD"
            ],
            '-129-3' => [
                -129,
                3,
                "\xFF\xFF\x7F",
                "FFFF7F",
                3,
                false,
                "FFFF7F"
            ],
        ];
    }

    public function testNewFromHex(): void
    {
        $this->assertEquals(
            new BinaryString("\x00\x12\xab"),
            BinaryString::newFromHex("\n00  \t\r\r12   ab  \t")
        );
    }

    public function testNewFromFourBitString(): void
    {
        $fourBitString = "12?>=<;:";

        $binaryString = BinaryString::newFromFourBitString($fourBitString);

        $this->assertEquals(
            new BinaryString("\x12\xfe\xdc\xba"),
            $binaryString
        );

        $this->assertSame(
            $fourBitString,
            $binaryString->toFourBitString()
        );
    }

    /**
     * @dataProvider newFromBitsStringProvider
     */
    public function testNewFromBitsString($value, $expectedString): void
    {
        $binaryString = BinaryString::newFromBitsString($value);

        $this->assertSame($expectedString, (string)$binaryString);

        $this->assertSame(
            str_replace(' ', '', $value),
            $binaryString->toBitsString()
        );
    }

    public function newFromBitsStringProvider(): array
    {
        return [
            [ '', '' ],
            [ '1000 0001', '81' ],
            [ '1111 0000 1001 0110', 'F096' ],
            [ '0001 0010 0011 0100 0101 0110', '123456' ]
        ];
    }

    /**
     * @dataProvider newFromBitsSetProvider
     */
    public function testNewFromBitsSet(
        $bitsSet,
        $leftmostBitIndex,
        $expectedString): void {
        $binaryString =
            BinaryString::newFromBitsSet($bitsSet, $leftmostBitIndex);

        $this->assertSame($expectedString, (string)$binaryString);

        if (is_array($bitsSet)) {
            sort($bitsSet);

            $bitsSet = new Set($bitsSet);
        }

        $this->assertEquals(
            $bitsSet,
            $binaryString->toBitsSet($leftmostBitIndex)
        );
    }

    public function newFromBitsSetProvider(): array
    {
        return [
            [ [], 0, '' ],
            [ new Set(), 1, '' ],
            [ [ 8, 9 ], 0, "00C0" ],
            [ new Set([ 4, 42, 44, 45 ]), 1, "100000000058" ]
        ];
    }

    public function testNewFromBitsStringException(): void
    {
        $this->expectException(Unsupported::class);
        $this->expectExceptionMessage(
            '"Bit strings with length not a multipl..." not supported '
                . 'in "1010101"'
        );

        BinaryString::newFromBitsString('1010101');
    }

    public function testArrayAccess(): void
    {
        $binaryString = BinaryString::newFromHex("01020304");

        $this->assertTrue(isset($binaryString[0]));
        $this->assertTrue(isset($binaryString[3]));
        $this->assertFalse(isset($binaryString[4]));

        $this->assertSame(1, $binaryString[0]);
        $this->assertSame(2, $binaryString[1]);
        $this->assertSame(3, $binaryString[2]);
        $this->assertSame(4, $binaryString[3]);

        $binaryString[2] = 255;
        $this->assertSame(255, $binaryString[2]);
    }

    public function testArrayOffsetSetException1(): void
    {
        $binaryString = BinaryString::newFromHex("ABCD");

        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value 2 out of range [0, 1]; offset outside of given binary string'
        );

        $binaryString[2] = 0;
    }

    public function testArrayOffsetSetException2(): void
    {
        $binaryString = BinaryString::newFromHex("ABCD");

        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value 256 out of range [0, 255]; value does not represent a byte'
        );

        $binaryString[1] = 256;
    }

    public function testArrayOffsetUnsetException(): void
    {
        $binaryString = BinaryString::newFromHex("00");

        $this->expectException(Unsupported::class);
        $this->expectExceptionMessage(
            '"Unsetting bytes in a binary string" not supported'
        );

        unset($binaryString[0]);
    }

    /**
     * @dataProvider toIntProvider
     */
    public function testToInt(
        $hexString,
        $isSigned,
        $expectedInt
    ): void {
        $binaryString = BinaryString::newFromHex($hexString);

        $this->assertSame($expectedInt, $binaryString->toInt($isSigned));
    }

    public function toIntProvider()
    {
        return [
            [ "01", false, 0x01 ],
            [ "1234", false, 0x1234 ],
            [ "123456", false, 0x123456 ],
            [ "12345678", false, 0x12345678 ],
            [ "0123456789", false, 0x0123456789 ],
            [ "123456789012", false, 0x123456789012 ],
            [ "12345678901234", false, 0x12345678901234 ],
            [ "1234567890123456", false, 0x1234567890123456 ],
            [ "0000000000000000000000000000000123", false, 0x123 ],
            [ "01", true, 0x01 ],
            [ "FE", true, -2 ],
            [ "FFFFFD", true, -3 ],
            [ "FFFFFFFFFFFFFFFFFFFF8000", true, -32768 ],
        ];
    }

    public function testToIntException(): void
    {
        $binaryString = BinaryString::newFromHex("123456781234567812345678");

        $this->expectException(LengthOutOfRange::class);
        $this->expectExceptionMessage(
            'Length 12 of "123456781234567812345678" out of range [0, 8]; '
                . 'too long for conversion to integer'
        );

        $binaryString->toInt();
    }

    /**
     * @dataProvider trimProvider
     */
    public function testTrim(
        $hexString,
        $characters,
        $expectedLtrimHex,
        $expectedRtrimHex,
        $expectedTrimHex
    ): void {
        $binaryString = BinaryString::newFromHex($hexString);

        $this->assertEquals(
            BinaryString::newFromHex($expectedLtrimHex),
            $binaryString->ltrim($characters)
        );

        $this->assertEquals(
            BinaryString::newFromHex($expectedRtrimHex),
            $binaryString->rtrim($characters)
        );

        $this->assertEquals(
            BinaryString::newFromHex($expectedTrimHex),
            $binaryString->trim($characters)
        );
    }

    public function trimProvider(): array
    {
        return [
            [ '00FF1234EE00', null, 'FF1234EE00', '00FF1234EE', 'FF1234EE' ],
            [
                'FFEE001234FF00EE',
                "\xFF\xEE",
                '001234FF00EE',
                'FFEE001234FF00',
                '001234FF00'
            ]
        ];
    }

    /**
     * @dataProvider bitwiseAndProvider
     */
    public function testBitwiseAnd(
        $hexString1,
        $hexString2,
        $expectedResultHexString
    ): void {
        $binaryString1 = BinaryString::newFromHex($hexString1);
        $binaryString2 = BinaryString::newFromHex($hexString2);

        $result = $binaryString1->bitwiseAnd($binaryString2);

        $this->assertSame($expectedResultHexString, (string)$result);
    }

    public function bitwiseAndProvider(): array
    {
        return [
            [ "13", "31", "11" ],
            [ "0507", "F3", "0003" ],
            [
                "06",
                "1234567890123456789012345678901234567895",
                "0000000000000000000000000000000000000004"
            ]
        ];
    }

    /**
     * @dataProvider bitwiseOrProvider
     */
    public function testBitwiseOr(
        $hexString1,
        $hexString2,
        $expectedResultHexString
    ): void {
        $binaryString1 = BinaryString::newFromHex($hexString1);
        $binaryString2 = BinaryString::newFromHex($hexString2);

        $result = $binaryString1->bitwiseOr($binaryString2);

        $this->assertSame($expectedResultHexString, (string)$result);
    }

    public function bitwiseOrProvider(): array
    {
        return [
            [ "1144", "2211", "3355" ],
            [ "123456", "07", "123457" ],
            [
                "8400000000000000000000000000000000000000000000000012",
                "FF66",
                "840000000000000000000000000000000000000000000000FF76"
            ]
        ];
    }
}
