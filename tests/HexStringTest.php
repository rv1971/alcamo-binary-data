<?php

namespace alcamo\binary_data;

use PHPUnit\Framework\TestCase;
use alcamo\exception\SyntaxError;

class HexStringTest extends TestCase
{
    public function testNewFromBinaryString()
    {
        $hexString = HexString::newFromBinaryString("\x42\xab\x00\n");

        $this->assertSame('42AB000A', (string)($hexString));
    }

    public function testConstruct()
    {
        $hexString = HexString::newFromHexString("ab\tcd\r\n   1234 56 78 Cdf");

        $this->assertSame('ABCD12345678CDF', (string)$hexString);
    }

    public function testConstructException()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "12ABCDX789FF" at offset 6 ("X789FF"); '
                . 'not a valid hex string'
        );

        HexString::newFromHexString('12abcdx789ff');
    }

    public function testOffsetSet()
    {
        $hexString = HexString::newFromHexString('98 76 5f ed');

        $hexString[0] = 'a';
        $hexString[5] = '0';

        $this->assertSame('A87650ED', (string)$hexString);

        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "X" at offset 0 ("X"); not a valid hex digit'
        );

        $hexString[1] = 'X';
    }

    public function testToBinaryString()
    {
        $hexString = HexString::newFromHexString('abcd1234');

        $this->assertEquals(
            new BinaryString("\xab\xcd\x12\x34"),
            $hexString->toBinaryString()
        );
    }
}
