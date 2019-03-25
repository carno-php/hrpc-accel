<?php
/**
 * Reconciled of packets
 * User: moyo
 * Date: 2018/8/1
 * Time: 10:08 PM
 */

namespace Carno\HRPC\Accel\Transport;

use Throwable;

class Reconciled
{
    /**
     * @var Packet[]
     */
    private $packets = [];

    /**
     * @param int $seq
     * @return Packet
     */
    public function wait(int $seq) : Packet
    {
        return $this->packets[$seq] = new Packet($seq, $this);
    }

    /**
     * @param int $seq
     * @param string $message
     * @return bool
     */
    public function done(int $seq, string $message) : bool
    {
        if ($packet = $this->packets[$seq] ?? null) {
            unset($this->packets[$seq]);
            return $packet->received($message);
        }
        return false;
    }

    /**
     * @param int $seq
     * @param Throwable $e
     * @return bool
     */
    public function fail(int $seq, Throwable $e = null) : bool
    {
        if ($packet = $this->packets[$seq] ?? null) {
            unset($this->packets[$seq]);
            return $packet->failure($e);
        }
        return false;
    }

    /**
     * @param int $seq
     */
    public function free(int $seq) : void
    {
        unset($this->packets[$seq]);
    }

    /**
     * @param Throwable $e
     */
    public function shutdown(Throwable $e = null) : void
    {
        foreach (array_keys($this->packets) as $seq) {
            $this->fail($seq, $e);
        }
    }
}
