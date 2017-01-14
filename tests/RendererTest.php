<?php

/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 8:48 AM
 */

namespace WF\Hypernova\Tests;

class RendererTest extends \PHPUnit\Framework\TestCase
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

    public function testCreateJobs() {
        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        $job = new \WF\Hypernova\Job('myView', 'my_component', []);

        $plugin->expects($this->exactly(1))
            ->method('getViewData')
            ->with($this->equalTo($job))
            ->willReturn($job);

        $this->renderer->addPlugin($plugin);
        $this->renderer->addJob($job);

        $this->assertEquals([$job], $this->renderer->createJobs());
    }
}