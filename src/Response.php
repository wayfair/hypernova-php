<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 12:58 PM
 */

namespace WF\Hypernova;

class Response
{
    /**
     * @var \Exception
     */
    public $error;

    /**
     * @var \WF\Hypernova\JobResult[]
     */
    public $results;
}
