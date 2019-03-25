<?php
/**
 * Router modifier
 * User: moyo
 * Date: 2019-01-23
 * Time: 11:01
 */

namespace Carno\HRPC\Accel\Modifier;

use Carno\HRPC\Accel\Routing;
use Carno\HRPC\Client\Contracts\Modify;
use Carno\HRPC\Client\Endpoints;

class Router implements Modify
{
    /**
     * @param Endpoints $eps
     */
    public function handle(Endpoints $eps) : void
    {
        $eps->routing()->typeset()->extend(new Routing);
    }
}
