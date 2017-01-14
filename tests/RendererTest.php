<?php

/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 8:48 AM
 */

namespace WF\Hypernova\Tests;

use WF\Hypernova\Job;

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

        $job = new Job('myView', 'my_component', []);

        $plugin->expects($this->exactly(1))
            ->method('getViewData')
            ->with($this->equalTo($job))
            ->willReturn($job);

        $this->renderer->addPlugin($plugin);
        $this->renderer->addJob($job);

        $this->assertEquals([$job], $this->renderer->createJobs());
    }

    public function testMultipleJobsGetCreated() {
        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        foreach([1,2,3,4,5] as $id) {
            $this->renderer->addJob(new Job('myView' . $id, 'my_component', []));
        }

        $plugin->expects($this->exactly(5))
            ->method('getViewData');

        $this->renderer->addPlugin($plugin);

        $this->renderer->createJobs();
    }
}