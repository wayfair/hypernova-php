<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/15/17
 * Time: 12:07 AM
 */

namespace WF\Hypernova\Tests;

use WF\Hypernova\Job;
use WF\Hypernova\JobResult;

class JobResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider badServerResultProvider
     * @expectedException \InvalidArgumentException
     */
    public function testFromServerResultBadData($data)
    {
        JobResult::fromServerResult($data, new Job('foo', []));
    }

    public function badServerResultProvider()
    {
        return [
            [null],
            [['something' => 'foobar']],
            [['error' => null, 'html' => null]]
        ];
    }

    public function testFromServerResultPopulates()
    {
        $originalJob = new Job('foo', []);
        $jobResult = JobResult::fromServerResult(['success' => true, 'html' => '<div>data</div>', 'error' => null], $originalJob);

        $this->assertEquals('<div>data</div>', $jobResult->html);
        $this->assertEmpty($jobResult->error);
        $this->assertEquals($originalJob, $jobResult->originalJob);
    }

    public function testToString() {
        $originalJob = new Job('foo', []);
        $jobResult = JobResult::fromServerResult(['success' => true, 'html' => '<div>data</div>', 'error' => null], $originalJob);

        $this->assertEquals('<div>data</div>', (string) $jobResult);
    }
}
