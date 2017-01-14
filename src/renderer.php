<?php


/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/13/17
 * Time: 3:23 PM
 */

namespace WF\Hypernova;

class Renderer
{

    /**
     * @var array default configuration
     */
    protected static $config_defaults = [];

    /**
     * @var \WF\Hypernova\Plugins\Plugin[]
     */
    protected $plugins;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $incomingJobs = [];

    /**
     * Renderer constructor.
     *
     * @param $url
     * @param array $plugins
     * @param array $config
     */
    public function __construct($url, $plugins = [], $config = [])
    {
        $this->url = $url;
        $this->plugins = $plugins;
        $this->config = array_merge(self::$config_defaults, $config);
    }

    /**
     * Add a plugin
     *
     * @param \WF\Hypernova\Plugins\Plugin $plugin The plugin
     *
     * @return void
     */
    public function addPlugin(\WF\Hypernova\Plugins\Plugin $plugin)
    {
        $this->plugins[] = $plugin;
    }

    /**
     * Add a job
     *
     * @param $job { [view]: { name: String, data: ReactProps } }
     */
    public function addJob($job)
    {
        $this->incomingJobs[] = $job;
    }

    /**
     * Do the things.
     *
     * @return array
     */
    public function render()
    {
        $jobs = $this->createJobs();
        list($shouldSendRequest, $jobs) = $this->prepareRequest($jobs);
        if (!$shouldSendRequest) {

        }
    }

    /**
     * @return array
     */
    protected function createJobs()
    {
        $jobs = [];
        foreach ($this->incomingJobs as $viewName => $job) {
            $data = $job->data;
            foreach ($this->plugins as $plugin) {
                $data = $plugin->getViewData($viewName, $job->data);
            }
            $jobs[$viewName] = ['name' => $job->name, 'data' => $data];
        }

        return $jobs;
    }

    /**
     * @param $jobs
     * @return array
     */
    protected function prepareRequest($jobs)
    {
        $prepared_jobs = array_map(function($job) use ($this) {
            foreach($this->plugins as $plugin) {
                $job = $plugin->prepareRequest($job);
            }
            return $job;
        }, $jobs);

        $shouldSend = true;
        foreach ($this->plugins as $plugin) {
            $shouldSend = $shouldSend && $plugin->shouldSendRequest($jobs);
        }

        return [$shouldSend, $prepared_jobs];
    }
}