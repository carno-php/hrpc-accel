<?php
/**
 * Feature marking for service discovery
 * User: moyo
 * Date: 2018/8/1
 * Time: 11:13 AM
 */

namespace Carno\HRPC\Accel;

use Carno\Cluster\Contracts\Tags;
use Carno\HRPC\Accel\Contracts\Named;

class Marking
{
    /**
     * @param int $port
     * @return string
     */
    public static function viaTCP(int $port) : string
    {
        return Tags::CMD . sprintf('%s:%s::%d', Named::KEY, Named::TCP, $port);
    }

    /**
     * @param string $tagging
     * @return int
     */
    public static function commTCP(string $tagging) : int
    {
        if (substr($tagging, 0, strlen(Named::KEY)) === Named::KEY) {
            [1 => $proto, 3 => $port] = explode(':', $tagging);
            switch ($proto) {
                case Named::TCP:
                    return $port;
            }
        }
        return 0;
    }
}
