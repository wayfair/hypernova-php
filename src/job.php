<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 8:57 AM
 */

namespace WF\Hypernova;


class Job implements \JsonSerializable
{
    public $name;

    public $data;


    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * Factory to create from ['viewName' => ['name' => $name, 'data' => $data]]
     *
     * @param array $arr input array
     *
     * @return \WF\Hypernova\Job
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $arr)
    {
        if (empty($arr['name']) || !isset($arr['data'])) {
            throw new \InvalidArgumentException('malformed job');
        }

        return new static($arr['name'], $arr['data']);
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'data' => $this->data
        ];
    }
}