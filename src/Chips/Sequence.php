<?php
/**
 * TCP accel sequence
 * User: moyo
 * Date: 2018/7/30
 * Time: 4:42 PM
 */

namespace Carno\HRPC\Accel\Chips;

trait Sequence
{
    /**
     * @var int
     */
    private $seq = 0;

    /**
     * @return int
     */
    protected function seq() : int
    {
        return $this->seq ++ > 0xFFFFFFFF ? $this->seq = 1 : $this->seq;
    }
}
