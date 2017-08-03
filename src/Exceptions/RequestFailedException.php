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

namespace BrianFaust\Unified\Exceptions;

use Exception;

/**
 * Class RequestFailedException.
 */
class RequestFailedException extends Exception
{
    /**
     * @var array
     */
    private $response;

    /**
     * RequestFailedException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     * @param array          $response
     */
    public function __construct($message, $code = 0, Exception $previous = null, $response = [])
    {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }
}
