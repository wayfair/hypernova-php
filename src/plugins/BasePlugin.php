<?php

/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/13/17
 * Time: 4:21 PM
 */

namespace WF\Hypernova\Plugins;

class BasePlugin implements Plugin
{
    /**
     * {@inheritdoc}
     */
    public function prepareRequest(array $jobs, array $originalJobs)
    {
        return $jobs;
    }

    /**
     * {@inheritdoc}
     */
    public function onError($error, array $jobOrJobs)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onSuccess($jobResult)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function afterResponse($jobResults)
    {
        return $jobResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewData($name, array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSendRequest($jobs)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function willSendRequest($jobs)
    {
    }
}
