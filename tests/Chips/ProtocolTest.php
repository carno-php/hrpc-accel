<?php

namespace Carno\HRPC\Accel\Tests\Chips;

use Carno\HRPC\Accel\Chips\Protocol;
use PHPUnit\Framework\TestCase;

class ProtocolTest extends TestCase
{
    use Protocol;

    public function testPack()
    {
        $packed = $this->packing($seq1 = rand(0, 0xFFFFFFFF), $msg1 = uniqid('hw'));

        list($seq2, $msg2) = $this->unpacking($packed);

        $this->assertEquals($seq1, $seq2);
        $this->assertEquals($msg1, $msg2);
    }
}
