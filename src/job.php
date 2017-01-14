<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 8:57 AM
 */

namespace WF\Hypernova;


class Job
{

    public $viewName;

    public $name;

    public $data;


    public function __construct($viewName, $name, $data)
    {
        $this->viewName = $viewName;
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * Factory to create from ['viewName' => ['name' => $name, 'data' => $data]]
     *
     * @param array $arr
     *
     * @return \WF\Hypernova\Job
     * @throws \Exception
     */
    public static function fromArray(array $arr)
    {
        if (count($arr) !== 1) {
            throw new \Exception('malformed job');
        }

        foreach ($arr as $viewName => $args) {
            return new static($viewName, $args['name'], $args['data']);
        }
    }
}