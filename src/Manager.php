<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 4:19 下午.
 */

namespace HughCube\Laravel\ACM;

use HughCube\Laravel\ACM\Client\Client;
use HughCube\Laravel\AlibabaCloud\AlibabaCloud;
use HughCube\Laravel\AlibabaCloud\Client as AlibabaCloudClient;
use Illuminate\Support\Arr;

class Manager
{
    /**
     * The alifc server configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * The clients.
     *
     * @var Client[]
     */
    protected $clients = [];

    /**
     * Manager constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get a store by name.
     *
     * @param string|null $name
     *
     * @return Client
     */
    public function client($name = null)
    {
        $name = null == $name ? $this->getDefaultClient() : $name;

        if (isset($this->clients[$name])) {
            return $this->clients[$name];
        }

        return $this->clients[$name] = $this->resolve($name);
    }

    /**
     * Resolve the given store by name.
     *
     * @param string|null $name
     *
     * @return Client
     */
    protected function resolve($name = null)
    {
        return new Client($this->configuration($name));
    }

    /**
     * Get the default store name.
     *
     * @return string
     */
    public function getDefaultClient()
    {
        return Arr::get($this->config, 'default', 'default');
    }

    /**
     * Get the configuration for a store.
     *
     * @param string $name
     *
     * @return array
     * @throws \InvalidArgumentException
     *
     */
    protected function configuration($name = null)
    {
        $name = $name ?: $this->getDefaultClient();
        $clients = Arr::get($this->config, 'clients', []);
        $defaults = Arr::get($this->config, 'defaults', []);

        if (is_null($client = Arr::get($clients, $name))) {
            throw new \InvalidArgumentException("acm client [{$name}] not configured.");
        }

        return $this->formatConfig(array_merge($client, $defaults));
    }

    /**
     * @param array|string $config
     * @return mixed|string[]
     */
    protected function formatConfig($config)
    {
        if (is_string($config)) {
            $config = ['alibabaCloud' => $config];
        }

        $alibabaCloud = null;
        if (Arr::has($config, 'alibabaCloud') && $config['alibabaCloud'] instanceof AlibabaCloudClient) {
            $alibabaCloud = $config['alibabaCloud'];
        } elseif (Arr::has($config, 'alibabaCloud')) {
            $alibabaCloud = AlibabaCloud::client($config['alibabaCloud']);
        }

        /** AccessKeyID */
        if (empty($config['AccessKeyID']) && null !== $alibabaCloud) {
            $config['AccessKeyID'] = $alibabaCloud->getAccessKeyId();
        }

        /** AccessKeySecret */
        if (empty($config['AccessKeySecret']) && null !== $alibabaCloud) {
            $config['AccessKeySecret'] = $alibabaCloud->getAccessKeySecret();
        }

        return $config;
    }
}
