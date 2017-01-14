<?php

/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 8:48 AM
 */

namespace WF\Hypernova\Tests;

use WF\Hypernova\Job;
use WF\Hypernova\Renderer;

class RendererTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \WF\Hypernova\Renderer
     */
    private $renderer;

    private $defaultJob;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
        $this->renderer = new \WF\Hypernova\Renderer('http://localhost:8080/batch');
        $this->defaultJob = new Job('myView', 'my_component', []);
    }

    public function testCreateJobs() {
        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        $job = $this->defaultJob;

        $plugin->expects($this->once())
            ->method('getViewData')
            ->with($this->equalTo($job->data))
            ->willReturn($job->data);

        $this->renderer->addPlugin($plugin);
        $this->renderer->addJob($job);

        $this->assertEquals([$job], $this->renderer->createJobs());
    }

    public function testMultipleJobsGetCreated() {
        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        for($i = 0; $i < 5; $i++) {
            $this->renderer->addJob($this->defaultJob);
        }

        $plugin->expects($this->exactly(5))
            ->method('getViewData');

        $this->renderer->addPlugin($plugin);

        $this->renderer->createJobs();
    }

    public function testPrepareRequestCallsPlugin() {
        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        $plugin->expects($this->exactly(2))
            ->method('prepareRequest')
            ->with($this->equalTo($this->defaultJob))
            ->willReturn($this->defaultJob);

        $this->renderer->addPlugin($plugin);
        $this->renderer->addPlugin($plugin);

        $allJobs = [$this->defaultJob];

        $this->assertEquals($allJobs, $this->renderer->prepareRequest($allJobs)[1]);
    }

    public function testShouldSend() {
        $pluginDontSend = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);
        $pluginDoSend = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        $pluginDontSend->expects($this->once())
            ->method('shouldSendRequest')
            ->willReturn(false);

        $pluginDoSend->expects($this->never())
            ->method('shouldSendRequest');

        $this->renderer->addPlugin($pluginDontSend);
        $this->renderer->addPlugin($pluginDoSend);

        $this->assertFalse($this->renderer->prepareRequest([$this->defaultJob])[0]);
    }

    public function testRenderShouldNotSend() {
        $renderer = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareRequest', 'createJobs'])
            ->getMock();

        $renderer->expects($this->once())
            ->method('prepareRequest')
            ->willReturn([false, []]);

        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        foreach (['willSendRequest', 'onError', 'onSuccess', 'afterResponse'] as $methodThatShouldNotBeCalled) {
            $plugin->expects($this->never())
                ->method($methodThatShouldNotBeCalled);
        }

        $renderer->addPlugin($plugin);

        // TODO assert some things about response once I make that obj
        $response = $renderer->render();
    }

}