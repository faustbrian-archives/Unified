<?php

declare(strict_types=1);

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@brianfaust.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified;

use BrianFaust\Utils\Mapper;

/**
 * Class AbstractApi.
 */
abstract class AbstractApi
{
    /** @var HttpClient */
    protected $httpClient;

    /** @var string */
    protected $model;

    /** @var string */
    protected $mapper;

    /**
     * AbstractApi constructor.
     *
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->mapper = new Mapper($this->model);
    }

    /**
     * @return object
     */
    public function getMapper()
    {
        return $this->mapper;
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
}
