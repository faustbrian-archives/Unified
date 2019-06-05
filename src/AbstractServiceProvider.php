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

namespace BrianFaust\Unified;

use BrianFaust\Unified\Contracts\HttpClient;
use League\Container\ServiceProvider\AbstractServiceProvider as ServiceProvider;

/**
 * Class AbstractServiceProvider.
 */
abstract class AbstractServiceProvider extends ServiceProvider
{
    /** @var array */
    protected $provides = [HttpClient::class];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->getContainer()->add(HttpClient::class, $this->getHttpClient());
    }

    /**
     * @return string
     */
    protected function getHttpClient()
    {
        return str_replace('ServiceProvider', 'HttpClient', get_called_class());
    }

    /**
     * @return string
     */
    abstract protected function getIdentifier();
}
