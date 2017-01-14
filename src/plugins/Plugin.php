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
    public function getViewData($name, $data);

    public function prepareRequest($request);

    public function shouldSendRequest($jobs);

    /**
     * @param $jobs
     * @return void
     */
    public function willSendRequest($jobs);

    public function onError($error, $jobs);

    public function onSuccess($response, $jobs);

    public function afterResponse($currentResponse, $originalResponse);
}