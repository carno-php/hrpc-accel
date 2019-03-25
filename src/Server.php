<?php
/**
 * TCP accelerator [server]
 * User: moyo
 * Date: 2018/7/30
 * Time: 12:31 PM
 */

namespace Carno\HRPC\Accel;

use Carno\HRPC\Accel\Chips\Protocol;
use Carno\HRPC\Accel\Chips\Sequence;
use Carno\HRPC\Accel\Chips\Specification;
use Carno\HRPC\Accel\Contracts\Config;
use Carno\HRPC\Accel\Exception\TransportNowClosedException;
use Carno\HRPC\Accel\Transport\Reconciled;
use Carno\HTTP\Server\Connection;
use Carno\Net\Address;
use Carno\Net\Contracts\HTTP;
use Carno\Net\Events;
use Carno\Socket\Contracts\Stream;
use Carno\Socket\Options;
use Carno\Socket\Powered\Swoole\Server as Socket;
use Psr\Http\Message\ResponseInterface as Response;
use Swoole\Server as SWServer;
use Throwable;

class Server implements HTTP
{
    use Protocol, Sequence, Specification;

    /**
     * @var Address
     */
    private $bind = null;

    /**
     * @var SWServer
     */
    private $master = null;

    /**
     * @var Events
     */
    private $events = null;

    /**
     * @var Reconciled
     */
    private $reconcile = null;

    /**
     * @var int
     */
    private $ported = 0;

    /**
     * Server constructor.
     * @param Address $bind
     * @param SWServer $master
     * @param Events $events
     */
    public function __construct(Address $bind, SWServer $master, Events $events)
    {
        $this->bind = $bind;
        $this->master = $master;
        $this->events = $events;
        $this->reconcile = new Reconciled;
    }

    /**
     * @return int
     */
    public function ported() : int
    {
        return $this->ported;
    }

    /**
     */
    public function serve() : void
    {
        $ported = $this->master->listen($this->bind->host(), $this->bind->port(), SWOOLE_SOCK_TCP);
        if ($ported instanceof SWServer\Port) {
            (new Socket)->porting(
                $this->master,
                $ported,
                (new Events)->attach(
                    Events\Socket::RECEIVED,
                    [$this, 'received']
                ),
                new Options(Config::SW_PACKAGE, Config::SW_SOCKET, Config::SOCK_SERVER)
            );
            $this->ported = $ported->port;
        }
    }

    /**
     */
    public function shutdown() : void
    {
        $this->reconcile->shutdown(new TransportNowClosedException);
    }

    /**
     * @param Stream $socket
     */
    public function received(Stream $socket) : void
    {
        [$seq, $message] = $this->unpacking($socket->recv());

        try {
            $request = $this->s2request($message);
        } catch (Throwable $e) {
            $socket->close();
            return;
        }

        $this->reconcile->wait($idx = $this->seq())->message()->then(function (string $message) use ($seq, $socket) {
            return $socket->write($this->packing($seq, $message));
        }, static function () use ($socket) {
            return $socket->close();
        });

        $this->events->notify(
            Events\HTTP::REQUESTING,
            (new Connection)
                ->setID($idx)
                ->setSEQ($seq)
                ->setRequest($request)
                ->setLocal($socket->local()->host(), $socket->local()->port())
                ->setRemote($socket->remote()->host(), $socket->remote()->port())
                ->setServiced($socket->serviced())
                ->from($this)
        );
    }

    /**
     * @param int $conn
     * @param Response $response
     * @return bool
     */
    public function reply(int $conn, Response $response) : bool
    {
        return $this->reconcile->done($conn, $this->response2s($response));
    }

    /**
     * @param int $conn
     * @return bool
     */
    public function close(int $conn) : bool
    {
        return $this->reconcile->fail($conn);
    }
}
