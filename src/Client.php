<?php
/**
 * TCP accelerator [client]
 * User: moyo
 * Date: 2018/7/30
 * Time: 3:10 PM
 */

namespace Carno\HRPC\Accel;

use function Carno\Coroutine\race;
use function Carno\Coroutine\timeout;
use Carno\HRPC\Accel\Chips\Protocol;
use Carno\HRPC\Accel\Chips\Sequence;
use Carno\HRPC\Accel\Chips\Specification;
use Carno\HRPC\Accel\Contracts\Named;
use Carno\HRPC\Accel\Transport\Packet;
use Carno\HRPC\Accel\Transport\Socket;
use Carno\HTTP\Exception\RequestTimeoutException;
use Carno\HTTP\Options;
use Carno\Net\Address;
use Carno\Net\Endpoint;
use Carno\Pool\Pool;
use Carno\Pool\Wrapper\SAR;
use Carno\Promise\Promised;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Client
{
    use SAR;
    use Protocol, Sequence, Specification;

    /**
     * @var Address
     */
    private $address = null;

    /**
     * @var Options
     */
    private $options = null;

    /**
     * @var Pool
     */
    private $sockets = null;

    /**
     * Client constructor.
     * @param Options $options
     * @param Endpoint $endpoint
     */
    public function __construct(Options $options, Endpoint $endpoint)
    {
        $this->address = $address = new Address($endpoint->address()->host(), $endpoint->option(Named::VIA_TCP));
        $this->options = $options;

        $this->sockets = new Pool($options->pooling(), static function () use ($address) {
            return new Socket($address);
        }, $options->identify());
    }

    /**
     * @return Address
     */
    public function restricted() : Address
    {
        return $this->address;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function perform(Request $request)
    {
        $seq = $this->seq();

        /**
         * @var Packet $packet
         * @see Socket::send
         */

        $packet = yield $this->sarRun(
            $this->sockets,
            'send',
            [$seq, $this->request2s($request)]
        );

        $message = yield race(
            $packet->message(),
            timeout($this->options->ttWait, RequestTimeoutException::class, 'SEQ='.$seq)
        );

        return $this->s2response($message);
    }

    /**
     * @return Promised
     */
    public function close() : Promised
    {
        return $this->sockets->shutdown();
    }
}
