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
     * @param mixed $data
     * @return mixed $data
     */
    public function getViewData($data);

    public function prepareRequest($request);

    /**
     * @param \WF\Hypernova\Job[] $jobs
     * @return bool
     */
    public function shouldSendRequest($jobs);

    /**
     * @param \WF\Hypernova\Job[] $jobs
     * @return void
     */
    public function willSendRequest($jobs);

    public function onError($error, $jobs);

    public function onSuccess($response, $jobs);

    public function afterResponse($currentResponse, $originalResponse);
}