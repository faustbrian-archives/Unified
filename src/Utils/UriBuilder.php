<?php

/*
 * This file is part of Unified.
 *
 * (c) Brian Faust <hello@brianfaust.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrianFaust\Unified\Utils;

use League\Uri\Components\Query;
use League\Uri\Modifiers\MergeQuery;
use League\Uri\Schemes\Http;

class UriBuilder
{
    public function create(string $uri, array $query)
    {
        $query = Query::createFromPairs($query);

        $uri = Http::createFromString($uri);

        $modifier = new MergeQuery($query->__toString());

        return (string) $modifier->__invoke($uri);
    }
}
