<?php

declare(strict_types=1);

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@basecode.sh>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified\Handlers;

/**
 * Class AbstractHandler.
 */
abstract class AbstractHandler
{
    /** @var \BrianFaust\HttpClient\AbstractHttpClient */
    protected $httpClient;

    /**
     * AbstractHandler constructor.
     *
     * @param $httpClient
     */
    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return mixed
     */
    abstract public function create();
}
