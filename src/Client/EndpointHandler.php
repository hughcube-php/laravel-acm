<?php

namespace HughCube\Laravel\ACM\Client;

/**
 * Class Endpoint.
 */
class EndpointHandler
{
    /**
     * 请求地址
     *
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Endpoint[]
     */
    protected $endpoints;

    /**
     * Endpoint constructor.
     *
     * @param string $host
     * @param int $port
     */
    public function __construct($host, $port, Client $client = null)
    {
        $this->endpoint = new Endpoint($host, $port);
        $this->client = $client;
    }

    public function fetch()
    {
        if (!empty($this->endpoints)) {
            return true;
        }

        $response = $this->client->request('GET', $this->endpoint->makeUrl('diamond-server/diamond'));
        foreach (explode("\n", $response->getBody()->getContents()) as $server) {
            $server = trim($server);
            if (empty($server)) {
                continue;
            }

            $parts = explode(':', $server);
            $parts[1] = (isset($parts[1]) && $parts[1]) ? $parts[1] : null;
            $this->endpoints[] = new Endpoint($parts[0], $parts[1]);
        }
    }

    /**
     * @return Endpoint|null
     */
    public function randomUrl()
    {
        $this->fetch();

        if (empty($this->endpoints)) {
            return null;
        }

        return $this->endpoints[random_int(0, (count($this->endpoints) - 1))];
    }
}
