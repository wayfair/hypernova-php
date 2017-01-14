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

    public $id;

    public $name;

    public $data;


    public function __construct($id, $name, $data)
    {
        $this->id = $id;
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
        if (count($arr) !== 1) {
            throw new \InvalidArgumentException('malformed job');
        }

        // Yes, this is intentional.
        foreach ($arr as $viewName => $args) {
            return new static($viewName, $args['name'], $args['data']);
        }
    }

    public function jsonSerialize()
    {
        return [$this->id => [
            'name' => $this->name,
            'data' => $this->data
        ]];
    }
}