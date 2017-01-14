<?php

/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 8:48 AM
 */

namespace WF\Hypernova\Tests;

class RendererTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \WF\Hypernova\Renderer
     */
    private $renderer;
    /**
     * {@inheritdoc}
     */
    public function setUp() {
        $this->renderer = new \WF\Hypernova\Renderer('http://localhost:8080/batch');
    }

    public function testTestsWork() {
        $this->assertEquals(1,1);
    }
}