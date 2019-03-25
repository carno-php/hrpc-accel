<?php
/**
 * TCP protocol config
 * User: moyo
 * Date: 2018/7/31
 * Time: 4:26 PM
 */

namespace Carno\HRPC\Accel\Contracts;

interface Config
{
    public const SW_PACKAGE = [
        'open_length_check' => true,
        'package_max_length' => 1048576, // 1MB
        'package_length_type' => 'N',
        'package_length_offset' => 4, // | seq || size || message |
        'package_body_offset' => 8, // seq + size
    ];

    public const SW_SOCKET = [
        'socket_buffer_size' => self::SW_PACKAGE['package_max_length'] * 4,
    ];

    public const SOCK_SERVER = [
        'buffer_output_size' => self::SW_PACKAGE['package_max_length'],
    ];

    public const JSON_CODEC_OPTS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
}
