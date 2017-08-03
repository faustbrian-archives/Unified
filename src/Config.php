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

use Illuminate\Support\Collection;

/**
 * Class Config.
 */
class Config
{
    /** @var Collection */
    private $attributes;

    /**
     * Config constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = new Collection($attributes);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->attributes->has($key)) {
            return $this->attributes->get($key);
        }
    }
}
