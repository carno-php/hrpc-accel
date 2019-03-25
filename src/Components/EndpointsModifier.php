<?php
/**
 * Cluster endpoints modifier
 * User: moyo
 * Date: 2019-01-21
 * Time: 10:13
 */

namespace Carno\HRPC\Accel\Components;

use Carno\Console\Component;
use Carno\Console\Contracts\Application;
use Carno\Console\Contracts\Bootable;
use Carno\Container\DI;
use Carno\HRPC\Accel\Modifier\Router;
use Carno\HRPC\Client\Clustered;
use Carno\RPC\Contracts\Client\Cluster;

class EndpointsModifier extends Component implements Bootable
{
    /**
     * @var array
     */
    protected $dependencies = [Cluster::class];

    /**
     * @param Application $app
     */
    public function starting(Application $app) : void
    {
        /**
         * @var Clustered $clustered
         */

        $clustered = DI::get(Cluster::class);

        $clustered->modifier(new Router);
    }
}
