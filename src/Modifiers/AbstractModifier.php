<?php

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@brianfaust.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified\Modifiers;

use BrianFaust\HttpClient;

abstract class AbstractModifier
{
    /** @var HttpClient */
    protected $httpClient;

    /** @var array */
    protected $arguments;

    /**
     * Modifier constructor.
     *
     * @param HttpClient $httpClient
     * @param array      $arguments
     */
    public function __construct(HttpClient $httpClient, array $arguments)
    {
        $this->httpClient = $httpClient;
        $this->arguments = $arguments;
    }

    /**
     * Modify the HTTP Client and return the modified instance.
     *
     * @return \BrianFaust\HttpClient
     */
    abstract public function apply();
}
