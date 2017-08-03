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

namespace BrianFaust\Unified\Serialisers;

/**
 * Interface Serialiser.
 */
interface Serialiser
{
    /**
     * @param array $input
     *
     * @return mixed
     */
    public function serialise(array $input);
}
