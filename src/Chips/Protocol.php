<?php
/**
 * TCP accel protocol
 * User: moyo
 * Date: 2018/7/30
 * Time: 4:41 PM
 */

namespace Carno\HRPC\Accel\Chips;

trait Protocol
{
    /**
     * @param int $seq
     * @param string $message
     * @return string
     */
    protected function packing(int $seq, string $message) : string
    {
        return pack('N', $seq) . pack('N', strlen($message)) . $message;
    }

    /**
     * @param string $packet
     * @return array [int:seq, string:message]
     */
    protected function unpacking(string $packet) : array
    {
        return [
            unpack('N', substr($packet, 0, 4))[1],
            substr($packet, 8, unpack('N', substr($packet, 4, 4))[1])
        ];
    }
}
