<?php
/**
 * Transport tags named
 * User: moyo
 * Date: 2018/7/31
 * Time: 10:31 PM
 */

namespace Carno\HRPC\Accel\Contracts;

interface Named
{
    // key in tags
    public const KEY = 'COMM';

    // flag in tags
    public const TCP = 'TCP';

    // make comm via TCP
    public const VIA_TCP = 'comm-via-tcp';
}
