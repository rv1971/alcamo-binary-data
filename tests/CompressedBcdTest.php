<?php

namespace alcamo\binary_data;

use PHPUnit\Framework\TestCase;
use alcamo\exception\{OutOfRange, SyntaxError};

class CompressedBcdTest extends TestCase
{
    /**
     * @dataProvider newFromIntProvider
     */
    public function testNewFromInt(
        $value,
        $minDigits,
        $allowOdd,
        $expectedString
    ) {
        $bcd = CompressedBcd::newFromInt($value, $minDigits, $allowOdd);

        $this->assertSame($expectedString, (string)$bcd);
    }

    public function newFromIntProvider()
    {
        return [
            'simple-0' => [ 0, null, null, '0F' ],
            'simple-1' => [ 1, null, null, '1F' ],
            'simple-42' => [ 42, null, null, '42' ],
            'simple-123456789' => [ 123456789, null, null, '123456789F' ],
            'minDigits-0' => [ 0, 3, null, '0FFF' ],
            'minDigits-42' => [ 42, 8, null, '42FFFFFF' ],
            'minDigits-12345' => [ 12345, 3, null, '12345F' ],
            'allowOdd-0' => [ 0, null, true, '0' ],
            'allowOdd-1' => [ 1, null, true, '1' ],
            'allowOdd-minDigits-123' => [ 123, 7, true, '123FFFF' ],
            'allowOdd-minDigits-1234567' => [ 1234567, 6, true, '1234567' ]
        ];
    }

    public function testConstruct()
    {
        $bcd = CompressedBcd::newFromString(
            "12 34 56 78 90 12 34 56 78 90 12 34 56 78 90 12 34 56 78 90 ff FF"
        );

        $this->assertSame(
            "1234567890123456789012345678901234567890FFFF",
            (string)$bcd
        );
    }

    public function testConstructException()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in "12A34567" at offset 2 ("A34567"); not a valid compressed BCD literal'
        );

        CompressedBcd::newFromString('12A34567');
    }

    public function testPad()
    {
        $bcd = CompressedBcd::newFromString('123');

        $this->assertSame('123F', (string)$bcd->pad());

        $this->assertSame('123FFF', (string)$bcd->pad(5));

        $this->assertSame('123FFFFF', (string)$bcd->pad(8));

        $this->assertSame('123FFFFFF', (string)$bcd->pad(9, true));

        $this->assertSame('123FFFFFFF', (string)$bcd->pad(9, true)->pad(6));

        $this->assertSame('123FFFFFFFF', (string)$bcd->pad(11, true));

        $this->assertSame(
            '123FFFFFFFF',
            (string)$bcd->pad(11, true)->pad(4, true)
        );
    }
}
