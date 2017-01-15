<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 1:04 PM
 */

namespace WF\Hypernova;


class JobResult
{
    /**
     * @var string
     */
    public $error;

    /**
     * @var string rendered HTML
     */
    public $html;

    /**
     * @var bool
     */
    public $success;

    /**
     * @var \WF\Hypernova\Job
     */
    public $originalJob;

    /**
     * @param $serverResult
     * @param array $originalJobData
     *
     * @return \WF\Hypernova\JobResult
     */
    public static function fromServerResult($serverResult, $originalJobData) {
        if (empty($serverResult->html) && empty($serverResult->error)) {
            throw new \InvalidArgumentException('Server result malformed');
        }

        $res = new static();

        $res->error = $serverResult->error;
        $res->html = $serverResult->html;
        $res->success = $serverResult->success;
        $res->originalJob = Job::fromArray([$originalJobData['name'] => $originalJobData['data']]);

        return $res;
    }
}