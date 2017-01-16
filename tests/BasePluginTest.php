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
    public function testPrepareRequest() {
        $plugin = new BasePlugin();

        $job = Job::fromArray(['name' => 'foo', 'data' => ['bar' => 'baz']]);

        $this->assertEquals($job, $plugin->prepareRequest($job));
    }

    public function testOnError() {
        $plugin = new BasePlugin();

        $plugin->onError(new \Exception('blah'), []);
    }

    public function testOnSuccess() {
        $plugin = new BasePlugin();

        $plugin->onSuccess($this->jobResult());
    }

    public function testAfterResponse() {
        $plugin = new BasePlugin();

        $jobResult = $this->jobResult();
        $this->assertEquals([$jobResult], $plugin->afterResponse([$jobResult]));
    }

    private function jobResult() {
        return JobResult::fromServerResult(
            ['html' => '<div>stuff</div>', 'error' => null, 'success' => true],
            Job::fromArray(['name' => 'foo', 'data' => []])
        );
    }
}