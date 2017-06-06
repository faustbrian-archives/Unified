<?php

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified\Handlers;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

/**
 * Class Curl.
 */
class Curl extends AbstractHandler
{
    /**
     * @return HandlerStack
     */
    public function create()
    {
        $handler = new CurlHandler($this->getConfiguration());

        return HandlerStack::create($handler);
    }

    /**
     * @return array
     */
    protected function getConfiguration()
    {
        return [];
    }
}
