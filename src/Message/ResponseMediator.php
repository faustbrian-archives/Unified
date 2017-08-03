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

namespace BrianFaust\Unified\Message;

use Psr\Http\Message\ResponseInterface;
use BrianFaust\Unified\Unserialisers\JsonUnserialiser;
use BrianFaust\Unified\Unserialisers\PlainUnserialiser;
use BrianFaust\Unified\Unserialisers\XmlUnserialiser;

/**
 * Class ResponseMediator.
 */
class ResponseMediator
{
    /** @var \Psr\Http\Message\ResponseInterface */
    private $response;

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return array|string
     */
    public function getContent()
    {
        $body = $this->response->getBody()->__toString();

        $contentType = $this->response->getHeaderLine('Content-Type');

        if ($this->isJson($body) || str_contains($contentType, 'application/json')) {
            return (new JsonUnserialiser())->unserialise($body);
        }

        if (str_contains($contentType, 'application/xml') || str_contains($contentType, 'text/xml')) {
            return (new XmlUnserialiser())->unserialise($body);
        }

        return (new PlainUnserialiser())->unserialise($body);
    }

    /**
     * Get the value for a single header.
     *
     * @param string $name
     *
     * @return string|null
     */
    public static function getHeader($name)
    {
        $headers = $this->response->getHeader($name);

        return array_shift($headers);
    }

    /**
     * Check if the given value is JSON. Required if the response contains no content type.
     *
     * @param string $value
     *
     * @return string|null
     */
    private function isJson($value)
    {
        json_decode($value);

        return json_last_error() == JSON_ERROR_NONE;
    }
}
