<?php

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified;

use BrianFaust\Unified\Message\ResponseMediator;
use BrianFaust\Unified\Utils\UriBuilder;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use League\Uri\Components\Query;
use Nyholm\Psr7\Factory\StreamFactory;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class AbstractHttpClient.
 */
abstract class AbstractHttpClient implements Contracts\HttpClient
{
    /** @var \Http\Message\MessageFactory */
    private $messageFactory;

    /** @var \Http\Message\StreamFactory */
    private $streamFactory;

    /** * @var Plugin[] */
    private $plugins = [];

    /** @var array */
    private $properties = [];

    /** @var array */
    protected $body = [];

    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $options = [];

    /** @var array */
    protected $requestModifiers = [];

    /** @var \BrianFaust\Client\Config */
    protected $config;

    /** @var \BrianFaust\Api\AbstractApi */
    protected $apiClass;

    /** @var array */
    private $methodMap = ['GET', 'HEAD', 'DELETE', 'PUT', 'PATCH', 'POST', 'OPTIONS'];

    /**
     * AbstractHttpClient constructor.
     */
    public function __construct()
    {
        $this->messageFactory = MessageFactoryDiscovery::find();
        $this->streamFactory = StreamFactoryDiscovery::find();
    }

    /**
     * It's magic!
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \Exception
     *
     * @return array|string
     */
    public function __call(string $method, array $arguments)
    {
        if (in_array($httpMethod = strtoupper($method), $this->methodMap)) {
            return $this->request($httpMethod, head($arguments));
        }

        throw new \Exception('Method "'.$method.'" does not exist.');
    }

    /**
     * @return string
     */
    protected function getHandler()
    {
        return Handlers\Curl::class;
    }

    /**
     * @param string $uri
     * @param string $path
     *
     * @return string
     */
    protected function buildRequestUri(string $uri, string $path)
    {
        return $uri.$path;
    }

    /**
     * @return HttpMethodsClient
     */
    private function getHttpClient()
    {
        // Begin by creating a Guzzle client, passing any configuration parameters you like:
        $this->options['defaults']['headers'] = $this->getHeaders();

        if (class_exists($handler = $this->getHandler())) {
            $handler = new $handler($this);

            $this->setHandler($handler->create());
        }

        $guzzle = new GuzzleClient($this->options);

        // Then create the adapter:
        $adapter = new GuzzleAdapter($guzzle);

        // make the cache plugin the last to be called
        $this->pushBackCachePlugin();

        $this->addPlugin(new Plugin\ErrorPlugin());

        // Finish by building the HttpMethodsClient:
        return new HttpMethodsClient(
            new PluginClient($adapter, $this->plugins),
            $this->messageFactory
        );
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $headers
     * @param array  $body
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function sendMultipartRequest(string $method, string $uri, array $headers, array $body)
    {
        $builder = new MultipartStreamBuilder($this->streamFactory);

        foreach ($body as $item) {
            $builder->addResource($item['name'], $item['contents']);
        }

        $multipartStream = $builder->build();
        $boundary = $builder->getBoundary();

        $request = $this->messageFactory->createRequest(
            $method,
            $uri,
            array_merge(['Content-Type' => 'multipart/form-data; boundary='.$boundary], $headers),
            $multipartStream
        );

        return $this->getHttpClient()->sendRequest($request);
    }

    /**
     * @param string $method
     * @param string $path
     *
     * @return array|string
     */
    private function request(string $method, string $path)
    {
        $modifiedClient = $this->applyModifiers([
            'method'      => $method,
            'path'        => $path,
            'form_params' => $this->getFormParameters(),
            'multipart'   => $this->getMultipart(),
            'query'       => $this->getQuery(),
            'json'        => $this->getJson(),
            'headers'     => $this->getHeaders(),
        ]);

        $modifiedClient->setHeaders($headers = $modifiedClient->getHeaders());

        $uri = $this->buildRequestUri($modifiedClient->options['base_uri'], $path);
        $body = null;

        // 1. Check if there are Query Parameters
        $dataQuery = $modifiedClient->getQuery();
        if (!empty($dataQuery)) {
            $uri = (new UriBuilder())->create($uri, $dataQuery);
        }

        // 2. Check if there are Form Parameters
        $dataFormParams = $modifiedClient->getFormParameters();
        if (!empty($dataFormParams)) {
            $body = \GuzzleHttp\Psr7\stream_for(http_build_query($dataFormParams));
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        // 3. Check if there are Multipart Parameters
        $dataMultipart = $modifiedClient->getMultipart();
        if (empty($dataMultipart)) {
            $response = $this->getHttpClient()->send($method, $uri, $headers, $body);
        } else {
            $response = $this->sendMultipartRequest($method, $uri, $headers, $dataMultipart);
        }

        return (new ResponseMediator($response))->getContent();
    }

    /**
     * @param array $arguments
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    private function applyModifiers(array $arguments)
    {
        $arguments = array_merge(get_object_vars($this->apiClass), $arguments);

        $modifiers = $this->getRequestModifier();

        $modifiedClient = clone $this;

        foreach ($modifiers as $modifier) {
            $modifiedClient = (new $modifier($modifiedClient, $arguments))->apply();
        }

        return $modifiedClient;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return array_get($this->body, 'query');
    }

    /**
     * @param $data
     */
    public function setQuery($data)
    {
        $this->body['query'] = array_merge(
            array_get($this->body, 'query', []), $data
        );

        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addQuery($key, $value)
    {
        $this->body['query'][$key] = $value;

        return $this;
    }

    public function flushQuery()
    {
        unset($this->body['query']);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormParameters()
    {
        return array_get($this->body, 'form_params');
    }

    /**
     * @param $data
     */
    public function setFormParameters($data)
    {
        $this->body['form_params'] = array_merge(
            array_get($this->body, 'form_params', []), $data
        );

        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addFormParameter($key, $value)
    {
        $this->body['form_params'][$key] = $value;

        return $this;
    }

    /**
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function flushFormParameters()
    {
        unset($this->body['form_params']);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJson()
    {
        return array_get($this->body, 'json');
    }

    /**
     * @param $data
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function setJson($data)
    {
        $this->body['json'] = array_merge(
            array_get($this->body, 'json', []), $data
        );

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function addJson($key, $value)
    {
        $this->body['json'][$key] = $value;

        return $this;
    }

    /**
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function flushJson()
    {
        unset($this->body['json']);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMultipart()
    {
        return array_get($this->body, 'multipart');
    }

    /**
     * @param $name
     * @param $contents
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function setMultipart($name, $contents)
    {
        $this->body['multipart'] = array_merge(
            array_get($this->body, 'multipart', []),
            [
                ['name' => $name, 'contents' => $contents],
            ]
        );

        return $this;
    }

    /**
     * @param $name
     * @param $contents
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function addMultipart($name, $contents)
    {
        $this->body['multipart'][] = compact('name', 'contents');

        return $this;
    }

    /**
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function flushMultipart()
    {
        unset($this->body['multipart']);

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $headers
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function setHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function addHeader($key, $value)
    {
        array_set($this->headers, $key, $value);

        return $this;
    }

    /**
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function flushHeaders()
    {
        unset($this->headers);

        return $this;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getOption($key)
    {
        return array_get($this->options, $key);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function setOption($key, $value)
    {
        array_set($this->options, $key, $value);

        return $this;
    }

    /**
     * @param $uri
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function setBaseUri($uri)
    {
        $this->setOption('base_uri', $uri);

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function setDefault($key, $value)
    {
        $this->setOption("defaults.$key", $value);

        return $this;
    }

    /**
     * @param $handler
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function setHandler($handler)
    {
        $this->setOption('handler', $handler);

        return $this;
    }

    /**
     * @param $modifier
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function addRequestModifier($modifier)
    {
        $this->requestModifiers[] = $modifier;

        return $this;
    }

    /**
     * @return array
     */
    public function getRequestModifier()
    {
        return $this->requestModifiers;
    }

    /**
     * @param $config
     */
    public function setConfig($config)
    {
        if (!$config instanceof Config) {
            $config = new Config($config);
        }

        $this->config = $config;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getConfig($key)
    {
        if (!empty($this->config) && !empty($key)) {
            return $this->config->$key;
        }

        return $this->config;
    }

    /**
     * @param $class
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function setApiClass($class)
    {
        $this->apiClass = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiClass()
    {
        return $this->apiClass;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function setProperty($key, $value)
    {
        array_set($this->properties, $key, $value);

        return $this;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getProperty($key)
    {
        return array_set($this->properties, $key);
    }

    /**
     * Add a new plugin to the end of the plugin chain.
     *
     * @param Plugin $plugin
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function addPlugin(Plugin $plugin)
    {
        $this->plugins[] = $plugin;

        return $this;
    }

    /**
     * Remove a plugin by its fully qualified class name (FQCN).
     *
     * @param string $fqcn
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function removePlugin($fqcn)
    {
        foreach ($this->plugins as $idx => $plugin) {
            if ($plugin instanceof $fqcn) {
                unset($this->plugins[$idx]);
            }
        }

        return $this;
    }

    /**
     * Add a cache plugin to cache responses locally.
     *
     * @param CacheItemPoolInterface $cache
     * @param array                  $config
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function addCache(CacheItemPoolInterface $cachePool, array $config = [])
    {
        $this->removeCache();
        $this->addPlugin(new Plugin\CachePlugin($cachePool, $this->streamFactory, $config));

        return $this;
    }

    /**
     * Remove the cache plugin.
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function removeCache()
    {
        $this->removePlugin(Plugin\CachePlugin::class);

        return $this;
    }

    /**
     * Make sure to move the cache plugin to the end of the chain.
     *
     * @return \BrianFaust\Unified\Contracts\HttpClient
     */
    public function pushBackCachePlugin()
    {
        $cachePlugin = null;

        foreach ($this->plugins as $i => $plugin) {
            if ($plugin instanceof Plugin\CachePlugin) {
                $cachePlugin = $plugin;

                unset($this->plugins[$i]);

                $this->plugins[] = $cachePlugin;

                return;
            }
        }

        return $this;
    }
}
