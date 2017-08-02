<?php

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@brianfaust.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified;

use League\Container\Container;
use ReflectionClass;

/**
 * Class AbstractClient.
 */
abstract class AbstractClient
{
    /** @var mixed|object */
    private $httpClient;

    /**
     * AbstractClient constructor.
     */
    public function __construct()
    {
        $this->container = new Container();
        $this->container->addServiceProvider($this->getServiceProvider());

        $this->httpClient = $this->container->get(HttpClient::class);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function api(string $name)
    {
        $reflector = new ReflectionClass(get_called_class());
        $namespace = $reflector->getNamespaceName();

        $class = $namespace.'\\Api\\'.$name;

        $apiClass = new $class($this->httpClient);

        $this->httpClient->setApiClass($apiClass);

        return $apiClass;
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return call_user_func_array([$this->httpClient, $method], $arguments);
    }

    /**
     * @return mixed
     */
    abstract protected function getServiceProvider();
}
