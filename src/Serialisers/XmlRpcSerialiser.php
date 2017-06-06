<?php

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified\Serialisers;

/**
 * Class XmlRpcSerialiser.
 */
class XmlRpcSerialiser implements Serialiser
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
        return xmlrpc_encode_request($input['method'], $input['parameters']);
    }
}
