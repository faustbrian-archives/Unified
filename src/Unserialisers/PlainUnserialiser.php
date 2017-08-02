<?php

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@brianfaust.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified\Unserialisers;

/**
 * Class PlainUnserialiser.
 */
class PlainUnserialiser implements Unserialiser
{
    /**
     * Unserialise an input.
     *
     * @param string $input
     *
     * @return string
     */
    public function unserialise(string $input)
    {
        return $input;
    }
}
