<?php
/**
 * TCP accel HTTP spec
 * User: moyo
 * Date: 2018/7/30
 * Time: 5:13 PM
 */

namespace Carno\HRPC\Accel\Chips;

use Carno\HRPC\Accel\Contracts\Config;
use Carno\HRPC\Accel\Exception\InvalidPacketException;
use Carno\HTTP\Standard\Helper;
use Carno\HTTP\Standard\Response;
use Carno\HTTP\Standard\ServerRequest;
use Carno\HTTP\Standard\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait Specification
{
    use Helper;

    /**
     * @param RequestInterface $request
     * @return string
     */
    protected function request2s(RequestInterface $request) : string
    {
        $server = $request->getUri()->getHost();

        [2 => $service, 3 => $method] = explode('/', $request->getUri()->getPath());

        return json_encode([
            'server' => $server,
            'service' => $service,
            'method' => $method,
            'status' => 0,
            'meta' => $this->getHeaderLines($request),
            'payload' => base64_encode($request->getBody()),
        ], Config::JSON_CODEC_OPTS);
    }

    /**
     * @param string $message
     * @return ServerRequestInterface
     * @throws InvalidPacketException
     */
    protected function s2request(string $message) : ServerRequestInterface
    {
        if (empty($packet = json_decode($message, true))) {
            throw new InvalidPacketException;
        }

        $srq = new ServerRequest(
            [],
            [],
            [],
            'POST',
            $packet['meta'],
            base64_decode($packet['payload'])
        );

        $srq->withUri(
            new Uri(
                'http',
                $packet['server'],
                null,
                sprintf('/invoke/%s/%s', $packet['service'], $packet['method'])
            )
        );

        return $srq;
    }

    /**
     * @param ResponseInterface $response
     * @return string
     */
    protected function response2s(ResponseInterface $response) : string
    {
        return json_encode([
            'server' => '',
            'service' => '',
            'method' => '',
            'status' => $response->getStatusCode(),
            'meta' => $this->getHeaderLines($response),
            'payload' => base64_encode($response->getBody()),
        ], Config::JSON_CODEC_OPTS);
    }

    /**
     * @param string $message
     * @return ResponseInterface
     * @throws InvalidPacketException
     */
    protected function s2response(string $message) : ResponseInterface
    {
        if (empty($packet = json_decode($message, true))) {
            throw new InvalidPacketException;
        }

        return new Response(
            $packet['status'],
            $packet['meta'],
            base64_decode($packet['payload'])
        );
    }
}
