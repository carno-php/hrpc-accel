<?php
/**
 * Transport socket
 * User: moyo
 * Date: 2018/8/1
 * Time: 5:01 PM
 */

namespace Carno\HRPC\Accel\Transport;

use Carno\HRPC\Accel\Chips\Protocol;
use Carno\HRPC\Accel\Contracts\Config;
use Carno\HRPC\Accel\Exception\TransportNowClosedException;
use Carno\Net\Address;
use Carno\Net\Events;
use Carno\Pool\Managed;
use Carno\Pool\Poolable;
use Carno\Promise\Promise;
use Carno\Promise\Promised;
use Carno\Socket\Contracts\Stream;
use Carno\Socket\Contracts\TClient;
use Carno\Socket\Options;
use Carno\Socket\Socket as Sock;

class Socket implements Poolable
{
    use Managed, Protocol;

    /**
     * @var Address
     */
    private $connect = null;

    /**
     * @var Promised
     */
    private $connected = null;

    /**
     * @var Reconciled
     */
    private $reconciled = null;

    /**
     * @var TClient
     */
    private $socket = null;

    /**
     * TCP constructor.
     * @param Address $connect
     */
    public function __construct(Address $connect)
    {
        $this->connect = $connect;
        $this->connected = Promise::deferred();

        $this->reconciled = new Reconciled;

        $this->closed()->sync($this->cleanup());
    }

    /**
     * @return Promised
     */
    public function connect() : Promised
    {
        $this->socket = Sock::connect(
            $this->connect,
            (new Events)
            ->attach(Events\Socket::CONNECTED, function () {
                $this->connected->resolve();
            })
            ->attach(Events\Socket::RECEIVED, function (Stream $conn) {
                $this->reconciled->done(...$this->unpacking($conn->recv()));
            })
            ->attach(Events\Socket::CLOSED, function () {
                $this->closed()->resolve();
            })
            ->attach(Events\Socket::ERROR, function () {
                $this->closed()->resolve();
            }),
            new Options(Config::SW_PACKAGE, Config::SW_SOCKET)
        );

        return $this->connected;
    }

    /**
     * @return Promised
     */
    public function heartbeat() : Promised
    {
        return Promise::resolved();
    }

    /**
     * @return Promised
     */
    public function close() : Promised
    {
        $this->socket->close();
        return $this->closed();
    }

    /**
     * @return Promised
     */
    private function cleanup() : Promised
    {
        ($w = Promise::deferred())->then(function () {
            $this->reconciled->shutdown(new TransportNowClosedException);
        });
        return $w;
    }

    /**
     * @param int $seq
     * @param string $message
     * @return Packet
     */
    public function send(int $seq, string $message) : Packet
    {
        $this->socket->write($this->packing($seq, $message));
        return $this->reconciled->wait($seq);
    }
}
