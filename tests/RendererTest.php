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
    public function setUp()
    {
        $this->renderer = new \WF\Hypernova\Renderer('http://localhost:8080/batch');
        $this->defaultJob = new Job('myView', 'my_component', []);
    }

    public function testCreateJobs()
    {
        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        $job = $this->defaultJob;

        $plugin->expects($this->once())
            ->method('getViewData')
            ->with($this->equalTo($job->name), $this->equalTo($job->data))
            ->willReturn($job->data);

        $this->renderer->addPlugin($plugin);
        $this->renderer->addJob($job);

        $this->assertEquals([$job], $this->renderer->createJobs());
    }

    public function testMultipleJobsGetCreated()
    {
        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        for ($i = 0; $i < 5; $i++) {
            $this->renderer->addJob($this->defaultJob);
        }

        $plugin->expects($this->exactly(5))
            ->method('getViewData');

        $this->renderer->addPlugin($plugin);

        $this->renderer->createJobs();
    }

    public function testPrepareRequestCallsPlugin()
    {
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

    public function testShouldSend()
    {
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

    public function testRenderShouldNotSend()
    {
        $renderer = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareRequest'])
            ->getMock();

        $renderer->expects($this->once())
            ->method('prepareRequest')
            ->willReturn([false, [$this->defaultJob]]);

        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        foreach (['willSendRequest', 'onError', 'onSuccess', 'afterResponse'] as $methodThatShouldNotBeCalled) {
            $plugin->expects($this->never())
                ->method($methodThatShouldNotBeCalled);
        }

        $renderer->addPlugin($plugin);

        /**
         * @var \WF\Hypernova\Response $response
         */
        $response = $renderer->render();

        $this->assertInstanceOf(\WF\Hypernova\Response::class, $response);
        $this->assertNull($response->error);

        $this->assertStringStartsWith('<div data-hypernova-key="my_component"', $response->results[0]->html);
    }

    public function testGetViewDataHandlesExceptions()
    {
        $plugin = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        $plugin->expects($this->once())
            ->method('getViewData')
            ->willThrowException(new \Exception('something went wrong'));

        $plugin->expects($this->once())
            ->method('onError');

        $this->renderer->addJob($this->defaultJob);
        $this->renderer->addPlugin($plugin);

        $this->assertEquals([$this->defaultJob], $this->renderer->createJobs());
    }


    /**
     * @dataProvider errorPluginProvider
     */
    public function testPrepareRequestErrorsCauseFallback($plugin)
    {
        $renderer = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['createJobs'])
            ->getMock();

        $renderer->expects($this->once())
            ->method('createJobs')
            ->willReturn([$this->defaultJob]);

        $renderer->addPlugin($plugin);

        /**
         * @var \WF\Hypernova\Response $response
         */
        $response = $renderer->render();

        $this->assertInstanceOf(\WF\Hypernova\Response::class, $response);
        $this->assertNotEmpty($response->error);

        $this->assertStringStartsWith('<div data-hypernova-key="my_component"', $response->results[0]->html);
    }

    public function errorPluginProvider()
    {
        $pluginThatThrowsInPrepareRequest = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        $pluginThatThrowsInPrepareRequest->expects($this->once())
            ->method('prepareRequest')
            ->willThrowException(new \Exception('Exception in prepare request'));

        $pluginThatThrowsInShouldSendRequest = $this->createMock(\WF\Hypernova\Plugins\BasePlugin::class);

        $pluginThatThrowsInShouldSendRequest->expects($this->once())
            ->method('shouldSendRequest')
            ->willThrowException(new \Exception('Exception in should send request'));

        foreach ([$pluginThatThrowsInPrepareRequest, $pluginThatThrowsInShouldSendRequest] as $plugin) {
            foreach (['willSendRequest', 'onError', 'onSuccess', 'afterResponse'] as $methodThatShouldNotBeCalled) {
                $plugin->expects($this->never())
                    ->method($methodThatShouldNotBeCalled);
            }
        }

        return [
            [$pluginThatThrowsInPrepareRequest],
            [$pluginThatThrowsInShouldSendRequest]
        ];
    }
}