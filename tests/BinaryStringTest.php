<?php

namespace alcamo\binary_data;

use PHPUnit\Framework\TestCase;
use alcamo\exception\{OutOfRange, Unsupported};

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
    ) {
        $binString = BinaryString::newFromInt($value, $minBytes);

        $this->assertSame($expectedToString, (string)$binString);

        $this->assertSame($expectedGetData, $binString->getData());

        $this->assertSame($expectedCount, count($binString));

        $this->assertSame($expectedIsZero, $binString->isZero());

        $this->assertSame($expectedLtrim, (string)$binString->ltrim());
    }

    public function newFromIntProvider()
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
            ]
        ];
    }

    public function testNewFromHex()
    {
        $this->assertEquals(
            new BinaryString("\x00\x12\xab"),
            BinaryString::newFromHex("\n00  \t\r\r12   ab  \t")
        );
    }

    public function testArrayAccess()
    {
        $binString = BinaryString::newFromHex("01020304");

        $this->assertTrue(isset($binString[0]));
        $this->assertTrue(isset($binString[3]));
        $this->assertFalse(isset($binString[4]));

        $this->assertSame(1, $binString[0]);
        $this->assertSame(2, $binString[1]);
        $this->assertSame(3, $binString[2]);
        $this->assertSame(4, $binString[3]);

        $binString[2] = 255;
        $this->assertSame(255, $binString[2]);
    }

    public function testArrayOffsetSetException1()
    {
        $binString = BinaryString::newFromHex("ABCD");

        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value "2" out of range [0, 1]; offset outside of given binary string'
        );

        $binString[2] = 0;
    }

    public function testArrayOffsetSetException2()
    {
        $binString = BinaryString::newFromHex("ABCD");

        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value "256" out of range [0, 255]; value does not represent a byte'
        );

        $binString[1] = 256;
    }

    public function testArrayOffsetUnsetException()
    {
        $binString = BinaryString::newFromHex("00");

        $this->expectException(Unsupported::class);
        $this->expectExceptionMessage(
            'Unsetting bytes in a binary string not supported'
        );

        unset($binString[0]);
    }

    /**
     * @dataProvider toIntProvider
     */
    public function testToInt(
        $hexString,
        $expectedInt
    ) {
        $binString = BinaryString::newFromHex($hexString);

        $this->assertSame($expectedInt, $binString->toInt());
    }

    public function toIntProvider()
    {
        return [
            [ "01", 0x01 ],
            [ "1234", 0x1234 ],
            [ "123456", 0x123456 ],
            [ "12345678", 0x12345678 ],
            [ "0123456789", 0x0123456789 ],
            [ "123456789012", 0x123456789012 ],
            [ "12345678901234", 0x12345678901234 ],
            [ "1234567890123456", 0x1234567890123456 ],
            [ "0000000000000000000000000000000123", 0x123 ]
        ];
    }

    public function testToIntException()
    {
        $binString = BinaryString::newFromHex("123456781234567812345678");

        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value "12" out of range [0, 8]; too long for conversion to integer'
        );

        $binString->toInt();
    }

    /**
     * @dataProvider bitwiseAndProvider
     */
    public function testBitwiseAnd(
        $hexString1,
        $hexString2,
        $expectedResultHexString
    ) {
        $binString1 = BinaryString::newFromHex($hexString1);
        $binString2 = BinaryString::newFromHex($hexString2);

        $result = $binString1->bitwiseAnd($binString2);

        $this->assertSame($expectedResultHexString, (string)$result);
    }

    public function bitwiseAndProvider()
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
    ) {
        $binString1 = BinaryString::newFromHex($hexString1);
        $binString2 = BinaryString::newFromHex($hexString2);

        $result = $binString1->bitwiseOr($binString2);

        $this->assertSame($expectedResultHexString, (string)$result);
    }

    public function bitwiseOrProvider()
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
