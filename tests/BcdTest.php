<?php

namespace alcamo\binary_data;

use PHPUnit\Framework\TestCase;
use alcamo\exception\{OutOfRange, SyntaxError};

class BcdTest extends TestCase
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
        $bcd = Bcd::newFromInt($value, $minDigits, $allowOdd);

        $this->assertSame($expectedString, (string)$bcd);
    }

    public function newFromIntProvider()
    {
        return [
            'simple-0' => [ 0, null, null, '00' ],
            'simple-1' => [ 1, null, null, '01' ],
            'simple-42' => [ 42, null, null, '42' ],
            'simple-123456789' => [ 123456789, null, null, '0123456789' ],
            'minDigits-0' => [ 0, 3, null, '0000' ],
            'minDigits-42' => [ 42, 8, null, '00000042' ],
            'minDigits-12345' => [ 12345, 3, null, '012345' ],
            'allowOdd-0' => [ 0, null, true, '0' ],
            'allowOdd-1' => [ 1, null, true, '1' ],
            'allowOdd-minDigits-123' => [ 123, 7, true, '0000123' ],
            'allowOdd-minDigits-1234567' => [ 1234567, 6, true, '1234567' ]
        ];
    }

    public function testConstruct()
    {
        $bcd = Bcd::newFromString(
            "12 34 56 78 90 12 34 56 78 90 12 34 56 78 90 12 34 56 78 90"
        );

        $this->assertSame(
            "1234567890123456789012345678901234567890",
            (string)$bcd
        );
    }

    public function testConstructException()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            "Syntax error in \"12A34567\" at 2: \"A34567\"; not a valid integer literal"
        );

        Bcd::newFromString('12A34567');
    }

    public function testToInt()
    {
        $this->assertSame(0, (Bcd::newFromString(''))->toInt());
        $this->assertSame(123, (Bcd::newFromString('123'))->toInt());
        $this->assertSame(456, (Bcd::newFromString('000456'))->toInt());
    }


    public function testToIntException()
    {
        $this->expectException(OutOfRange::class);
        $this->expectExceptionMessage(
            'Value "' . PHP_INT_MAX . '0" out of range [0, ' . PHP_INT_MAX
            . ']; unable to convert BCD to integer'
        );

        (Bcd::newFromString(PHP_INT_MAX . '0'))->toInt();
    }

    public function testPad()
    {
        $bcd = Bcd::newFromString('123');

        $this->assertSame('0123', (string)$bcd->pad());

        $this->assertSame('000123', (string)$bcd->pad(5));

        $this->assertSame('00000123', (string)$bcd->pad(8));

        $this->assertSame('000000123', (string)$bcd->pad(9, true));

        $this->assertSame('0000000123', (string)$bcd->pad(9, true)->pad(6));

        $this->assertSame('00000000123', (string)$bcd->pad(11, true));

        $this->assertSame(
            '00000000123',
            (string)$bcd->pad(11, true)->pad(4, true)
        );
    }
}
