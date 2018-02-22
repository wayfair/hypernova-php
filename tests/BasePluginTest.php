<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/15/17
 * Time: 7:47 PM
 */

namespace WF\Hypernova\Tests;

use WF\Hypernova\Job;
use WF\Hypernova\JobResult;
use WF\Hypernova\Plugins\BasePlugin;

class BasePluginTest extends \PHPUnit\Framework\TestCase
{
    public function testPrepareRequest()
    {
        $plugin = new BasePlugin();

        $job = Job::fromArray(['name' => 'foo', 'data' => ['bar' => 'baz']]);
        $jobs = [$job];

        $this->assertEquals($jobs, $plugin->prepareRequest($jobs, [$jobs]));
    }

    public function testOnError()
    {
        $plugin = new BasePlugin();

        $plugin->onError(new \Exception('blah'), []);
    }

    public function testOnSuccess()
    {
        $plugin = new BasePlugin();

        $plugin->onSuccess($this->makeJobResult());
    }

    public function testAfterResponse()
    {
        $plugin = new BasePlugin();

        $jobResult = $this->makeJobResult();
        $this->assertEquals([$jobResult], $plugin->afterResponse([$jobResult]));
    }

    public function testGetViewData()
    {
        $plugin = new BasePlugin();

        $data = ['foo' => 'bar'];

        $this->assertEquals($data, $plugin->getViewData('id1', $data));
    }

    public function testShouldSendRequest()
    {
        $plugin = new BasePlugin();

        $this->assertTrue($plugin->shouldSendRequest([$this->makeJob()]));
    }

    public function testWillSendRequest()
    {
        $plugin = new BasePlugin();

        $plugin->willSendRequest([$this->makeJob()]);
    }

    private function makeJobResult()
    {
        return JobResult::fromServerResult(
            ['html' => '<div>stuff</div>', 'error' => null, 'success' => true],
            $this->makeJob()
        );
    }

    private function makeJob()
    {
        return Job::fromArray(['name' => 'foo', 'data' => []]);
    }
}
