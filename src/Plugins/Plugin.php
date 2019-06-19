<?php

/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/13/17
 * Time: 4:18 PM
 */

namespace WF\Hypernova\Plugins;

interface Plugin
{
    /**
     * @param string $name
     * @param array $data
     *
     * @return array
     */
    public function getViewData($name, array $data);

    /**
     * @param \WF\Hypernova\Job[]   $jobs
     * @param \WF\Hypernova\Job[]   $originalJobs
     * @return \WF\Hypernova\Job
     */
    public function prepareRequest(array $jobs, array $originalJobs);

    /**
     * @param \WF\Hypernova\Job[] $jobs
     * @return bool
     */
    public function shouldSendRequest($jobs);

    /**
     * @param \WF\Hypernova\Job[] $jobs
     *
     * @return void
     */
    public function willSendRequest($jobs);

    /**
     * @param \Exception|mixed $error
     * @param \WF\Hypernova\Job[] $jobOrJobs
     *
     * @return void
     */
    public function onError($error, array $jobOrJobs);

    /**
     * @param \WF\Hypernova\JobResult $jobResult
     *
     * @return void
     */
    public function onSuccess($jobResult);

    /**
     * @param \WF\Hypernova\JobResult[] $jobResults
     *
     * @return \WF\Hypernova\JobResult[]
     */
    public function afterResponse($jobResults);
}
