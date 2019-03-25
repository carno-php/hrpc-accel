<?php
/**
 * Transport comm nodes classify
 * User: moyo
 * Date: 2018/7/31
 * Time: 6:36 PM
 */

namespace Carno\HRPC\Accel;

use Carno\Cluster\Routing\Typed;
use Carno\HRPC\Accel\Contracts\Named;
use Carno\HRPC\Client\Contracts\Defined;
use Carno\Net\Endpoint;

class Routing implements Typed
{
    /**
     * @param string ...$tags
     * @return array
     */
    public function picked(string ...$tags) : array
    {
        return [];
    }

    /**
     * @param string $tag
     * @param Endpoint $node
     */
    public function classify(string $tag, Endpoint $node) : void
    {
        if ($port = Marking::commTCP($tag)) {
            $node->setOptions([
                Defined::HJ_CLIENT => Client::class,
                Named::VIA_TCP => $port,
            ]);
        }
    }

    /**
     * @param Endpoint $node
     */
    public function release(Endpoint $node) : void
    {
        // do nothing
    }
}
