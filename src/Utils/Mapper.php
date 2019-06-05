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

namespace BrianFaust\Unified\Utils;

/**
 * Class Mapper.
 */
class Mapper
{
    /** @var string */
    private $model;

    /**
     * Mapper constructor.
     *
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @param $data
     *
     * @return object
     */
    public function raw($data)
    {
        return (object) $data;
    }

    /**
     * @param $data
     *
     * @return mixed|object
     */
    public function one($data)
    {
        return $this->model ? (new JsonMapper())->map($data, new $this->model())
                            : $this->raw($data);
    }

    /**
     * @param $data
     *
     * @return array|object
     */
    public function many($data)
    {
        return $this->model ? (new JsonMapper())->mapArray($data, [], $this->model)
                            : $this->raw($data);
    }
}
