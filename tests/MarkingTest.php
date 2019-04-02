<?php
/**
 * Tags marking test
 * User: moyo
 * Date: 2019-04-02
 * Time: 14:46
 */

namespace Carno\HRPC\Accel\Tests;

use Carno\HRPC\Accel\Marking;
use PHPUnit\Framework\TestCase;

class MarkingTest extends TestCase
{
    public function testTCPFlag()
    {
        $this->assertEquals('#COMM:TCP::123', Marking::viaTCP(123));
        $this->assertEquals(456, Marking::commTCP('COMM:TCP::456'));
    }
}
