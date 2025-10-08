<?php

namespace alcamo\binary_data;

use PHPUnit\Framework\TestCase;
use alcamo\exception\SyntaxError;

class EvenHexStringTest extends TestCase
{
    public function testConstruct()
    {
        $hexString =
            EvenHexString::newFromHexString("ab\tcd\r\n   1234 56 78 Cdf0");

        $this->assertSame('ABCD12345678CDF0', (string)$hexString);
    }

    public function testConstructException()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "123AB"; not an even number of hex digits'
        );

        EvenHexString::newFromHexString('12 3A B');
    }
}
