<?php

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified\Unserialisers;

/**
 * Interface Unserialiser.
 */
interface Unserialiser
{
    /**
     * @param string $input
     *
     * @return mixed
     */
    public function unserialise(string $input);
}