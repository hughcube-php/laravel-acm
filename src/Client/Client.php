<?php
/**
 * Created by IntelliJ IDEA.
 * User: hugh.li
 * Date: 2020/1/21
 * Time: 14:52.
 */

namespace HughCube\Laravel\ACM\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use HughCube\Laravel\ACM\Exceptions\HttpException;
use HughCube\PUrl\Url;
use Illuminate\Support\Arr;
use Psr\Http\Message\RequestInterface;

class Client
{
    /**
     * @val HttpClient
     */
    protected $httpClient;

    /**
     * @var EndpointHandler
     */
    protected $endpointHandler;

    /**
     * @var array
     */
    protected $config;

    /**
     * Client constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge($this->defaultConfig(), $config);
    }

    /**
     * @return EndpointHandler
     */
    public function getEndpointHandler()
    {
        if (!$this->endpointHandler instanceof EndpointHandler) {
            $this->endpointHandler = new EndpointHandler($this->getEndpoint(), null, $this);
        }

        return $this->endpointHandler;
    }

    /**
     * @return array
     */
    protected function defaultConfig()
    {
        return ['Endpoint' => 'acm.aliyun.com'];
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return Arr::get($this->config, 'Endpoint');
    }

    /**
     * @return string
     */
    public function getAccessKeyID()
    {
        return Arr::get($this->config, 'AccessKeyID');
    }

    /**
     * @return string
     */
    public function getAccessKeySecret()
    {
        return Arr::get($this->config, 'AccessKeySecret');
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return Arr::get($this->config, 'Namespace');
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return Arr::get($this->config, 'Group');
    }

    /**
     * @return string
     */
    public function getProxy()
    {
        return Arr::get($this->config, 'Proxy');
    }

    /**
     * @return Url
     */
    protected function makeUrl(string $path)
    {
        return $this->getEndpointHandler()->randomUrl()->makeUrl($path);
    }

    /**
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        if (!$this->httpClient instanceof HttpClient) {
            $handler = HandlerStack::create();
            $handler->push($this->signatureHandler());

            $this->httpClient = new HttpClient(['handler' => $handler, RequestOptions::PROXY => $this->getProxy()]);
        }

        return $this->httpClient;
    }

    /**
     * 添加请求头信息.
     *
     * @return \Closure
     */
    private function signatureHandler()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $request = $request
                    ->withHeader('timeStamp', time() * 1000)
                    ->withHeader('Spas-AccessKey', $this->getAccessKeyID());

                $signatureData = sprintf(
                    '%s+%s+%s',
                    $this->getNamespace(),
                    $this->getGroup(),
                    $request->getHeaderLine('timeStamp')
                );

                $signature = base64_encode(hash_hmac(
                    'sha1',
                    $signatureData,
                    $this->getAccessKeySecret(),
                    true
                ));

                $request = $request->withHeader('Spas-Signature', $signature);

                return $handler($request, $options);
            };
        };
    }

    /**
     * @param string $method
     * @param Url $uri
     * @param array $params
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws HttpException
     */
    public function request($method, Url $uri, array $params = [], array $options = [])
    {
        $params = array_merge($params, ['tenant' => $this->getNamespace(), 'group' => $this->getGroup()]);

        if ('GET' === strtoupper($method)) {
            $uri = $uri->withQueryArray($params);
        }

        if ('POST' === strtoupper($method) && !empty($params)) {
            $options[RequestOptions::FORM_PARAMS] = $params;
        }

        try {
            $response = $this->getHttpClient()->request($method, $uri, $options);
        } catch (\Throwable $exception) {
            throw new HttpException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $response;
    }

    /**
     * @param array $config
     * @return static
     */
    public function withConfig(array $config)
    {
        $class = static::class;
        return new $class(array_merge($this->config, $config));
    }

    /**
     * 读取配置集.
     *
     * @param string $dataId
     *
     * @return string
     * @throws \Exception
     *
     */
    public function read(string $dataId)
    {
        $response = $this->request(
            'GET',
            $this->makeUrl('diamond-server/config.co'),
            ['dataId' => $dataId]
        );

        return $response->getBody()->getContents();
    }

    /**
     * 写入配置集.
     *
     * @param string $dataId
     * @param string $content
     *
     * @return bool
     * @throws \Exception
     *
     */
    public function write(string $dataId, string $content)
    {
        $response = $this->request(
            'POST',
            $this->makeUrl('diamond-server/basestone.do?method=syncUpdateAll'),
            ['dataId' => $dataId, 'content' => $content]
        );

        return 200 == $response->getStatusCode();
    }

    /**
     * 删除配置集.
     *
     * @param string $dataId
     *
     * @return bool
     * @throws \Exception
     *
     */
    public function remove(string $dataId)
    {
        $response = $this->request(
            'POST',
            $this->makeUrl('diamond-server/datum.do?method=deleteAllDatums'),
            ['dataId' => $dataId]
        );

        return 200 == $response->getStatusCode();
    }

    /**
     * 监听配置集.
     *
     * @param string $dataId
     * @param string $content
     *
     * @return bool
     * @throws \Exception
     *
     */
    public function watch(string $dataId, string $content)
    {
        $wordDelimiter = chr(37) . chr(48) . chr(50);
        $lineDelimiter = chr(37) . chr(48) . chr(49);

        $args = [$dataId, $this->getGroup(), md5($content), $this->getNamespace()];

        $response = $this->request(
            'POST',
            $this->makeUrl('diamond-server/config.co'),
            ['Probe-Modify-Request' => implode($wordDelimiter, $args) . $lineDelimiter]
        );

        return 200 == $response->getStatusCode();
    }
}
