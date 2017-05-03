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

    public $metadata;


    public function __construct($name, $data, $metadata = [])
    {
        $this->name = $name;
        $this->data = $data;
        $this->metadata = $metadata;
    }

    /**
     * Factory to create from ['viewName' => ['name' => $name, 'data' => $data, 'metadata' => $metadata]]
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
        $metadata = isset($arr['metadata']) ? $arr['metadata'] : [];
        return new static($arr['name'], $arr['data'], $metadata);
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'data' => $this->data,
            'metadata' => $this->metadata
        ];
    }
}
