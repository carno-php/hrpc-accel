<?php
/**
 * Transport packet ops
 * User: moyo
 * Date: 2018/8/1
 * Time: 5:44 PM
 */

namespace Carno\HRPC\Accel\Transport;

use Carno\Promise\Promise;
use Carno\Promise\Promised;
use Throwable;

class Packet
{
    /**
     * @var int
     */
    private $seq = null;

    /**
     * @var Reconciled
     */
    private $from = null;

    /**
     * @var Promised
     */
    private $wait = null;

    /**
     * Packet constructor.
     * @param int $seq
     * @param Reconciled $from
     */
    public function __construct(int $seq, Reconciled $from)
    {
        $this->seq = $seq;
        $this->from = $from;
    }

    /**
     * @param string $message
     * @return bool
     */
    public function received(string $message) : bool
    {
        if ($this->wait && $this->wait->pended()) {
            $this->wait->resolve($message);
            $this->from = null;
            return true;
        }
        return false;
    }

    /**
     * @param Throwable $e
     * @return bool
     */
    public function failure(Throwable $e = null) : bool
    {
        if ($this->wait && $this->wait->pended()) {
            $this->wait->reject($e);
            $this->from = null;
            return true;
        }
        return false;
    }

    /**
     * @return Promised
     */
    public function message() : Promised
    {
        return $this->wait ?? $this->wait = Promise::deferred()->sync($this->cleanup());
    }

    /**
     * @return Promised
     */
    private function cleanup() : Promised
    {
        return Promise::deferred()->catch(function () {
            $this->from->free($this->seq);
            $this->from = null;
        });
    }
}
