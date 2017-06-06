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

use BrianFaust\Payload\Xml;

/**
 * Class XmlUnserialiser.
 */
class XmlUnserialiser implements Unserialiser
{
    /**
     * Unserialise an input.
     *
     * @param string $input
     *
     * @return array
     */
    public function unserialise(string $input)
    {
        return (new Xml())->unserialise($input);
    }
}
