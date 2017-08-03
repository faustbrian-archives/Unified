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

namespace BrianFaust\Unified\Handlers;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1 as GuzzleSubscriber;

/**
 * Class OAuth1.
 */
class OAuth1 extends AbstractHandler
{
    /**
     * @return HandlerStack
     */
    public function create()
    {
        $stack = HandlerStack::create();

        $middleware = new GuzzleSubscriber($this->getConfiguration());

        $stack->push($middleware);

        return $stack;
    }

    /**
     * @return array
     */
    protected function getConfiguration()
    {
        return [
            'consumer_key'    => $this->httpClient->getConfig('consumerKey'),
            'consumer_secret' => $this->httpClient->getConfig('consumerSecret'),
            'token'           => $this->httpClient->getConfig('token'),
            'token_secret'    => $this->httpClient->getConfig('tokenSecret'),
        ];
    }
}
