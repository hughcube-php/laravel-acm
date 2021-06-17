<?php

namespace HughCube\Laravel\ACM\Client;

use HughCube\PUrl\Url;

/**
 * Class Endpoint.
 */
class Endpoint
{
    /**
     * 请求地址
     *
     * @var string
     */
    protected $host;

    /**
     * 请求端口.
     *
     * @var int
     */
    protected $port;

    /**
     * Endpoint constructor.
     *
     * @param string $host
     * @param int    $port
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = empty($port) ? 8080 : $port;
    }

    /**
     * 生成完整URL.
     *
     * @param string $path
     *
     * @return Url
     */
    public function makeUrl(string $path)
    {
        return Url::instance()
            ->withHost($this->host)
            ->withPort($this->port)
            ->withPath($path);
    }
}
