<?php
/**
 * TCP accelerator for HRPC server
 * User: moyo
 * Date: 2018/7/30
 * Time: 12:07 PM
 */

namespace Carno\HRPC\Accel\Plugins;

use Carno\Consul\Types\Tagging;
use Carno\Container\DI;
use Carno\HRPC\Accel\Marking;
use Carno\HRPC\Accel\Server;
use Carno\Net\Address;
use Carno\Net\Contracts\Conn;
use Carno\Net\Events;
use Carno\RPC\Service\Dispatcher;
use Carno\Serving\Contracts\Plugins;

class ServerPorting implements Plugins
{
    /**
     * @var Address
     */
    private $listen = null;

    /**
     * @var Server
     */
    private $server = null;

    /**
     * ServerPorting constructor.
     * @param Address $listen
     */
    public function __construct(Address $listen)
    {
        $this->listen = $listen;
    }

    /**
     * @return bool
     */
    public function enabled() : bool
    {
        return !debug() &&
            $this->listen->valid() &&
            DI::has(Tagging::class) &&
            DI::has(Dispatcher::class) &&
            version_compare(SWOOLE_VERSION, '1.8.0') >= 0
        ;
    }

    /**
     * @param Events $events
     */
    public function hooking(Events $events) : void
    {
        $events
        ->attach(Events\Server::CREATED, function (Conn $serv) {
            ($this->server = new Server($this->listen, $serv->server()->raw(), $serv->events()))->serve();
            if ($port = $this->server->ported()) {
                /**
                 * @var Tagging $tagger
                 */
                $tagger = DI::get(Tagging::class);
                $tagger->setTags(Marking::viaTCP($port));
                logger('hrpc-accel')->info('TCP accelerator started', ['port' => $port]);
            } else {
                logger('hrpc-accel')->notice('TCP accelerator startup failed .. skip');
            }
        })
        ->attach(Events\Worker::STOPPED, function () {
            $this->server->shutdown();
        });
    }
}
