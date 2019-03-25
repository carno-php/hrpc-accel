<?php
/**
 * Serving extension
 * User: moyo
 * Date: 2019-01-15
 * Time: 19:25
 */

namespace Carno\HRPC\Accel;

use Carno\Console\Bootstrap;
use Carno\Console\Configure;
use Carno\HRPC\Accel\Components\EndpointsModifier;
use Carno\HRPC\Accel\Plugins\ServerPorting;
use Carno\Net\Address;
use Carno\Serving\Contracts\Extensions;
use Carno\Serving\Extension\Components;
use Carno\Serving\Extension\Plugins;
use Symfony\Component\Console\Input\InputOption;

class Extension implements Extensions
{
    /**
     * @param Configure $conf
     */
    public function options(Configure $conf) : void
    {
        $conf->addOption('hrpc-accel', null, InputOption::VALUE_OPTIONAL, 'TCP accelerator listen bind', ':0');
    }

    /**
     * @param Bootstrap $boot
     * @return Plugins
     */
    public function plugins(Bootstrap $boot) : Plugins
    {
        return new Plugins(
            new ServerPorting(new Address($boot->app()->input()->getOption('hrpc-accel')))
        );
    }

    /**
     * @return Components
     */
    public function components() : Components
    {
        return new Components(
            EndpointsModifier::class
        );
    }
}
