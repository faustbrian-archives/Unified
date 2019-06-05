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

namespace BrianFaust\Unified\Serialisers;

use BrianFaust\Payload\Json;

/**
 * Class JsonSerialiser.
 */
class JsonSerialiser implements Serialiser
{
    /**
     * Serialise an input.
     *
     * @param string $input
     *
     * @return string
     */
    public function serialise($input)
    {
        return (new Json())->serialise($input);
    }
}
