<?php


/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/13/17
 * Time: 3:23 PM
 */

namespace WF\Hypernova;

use GuzzleHttp\Exception\RequestException;

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
     * @var \WF\Hypernova\Job[]
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
     * @param \WF\Hypernova\Job|array $job Job to add, { [view]: { name: String, data: ReactProps } }
     */
    public function addJob($job)
    {
        if (is_array($job)) {
            $job = Job::fromArray($job);
        }
        $this->incomingJobs[] = $job;
    }

    /**
     * Do the things.
     *
     * @return \WF\Hypernova\Response
     */
    public function render()
    {
        $jobs = $this->createJobs();
        try {
            list($shouldSendRequest, $jobs) = $this->prepareRequest($jobs);
            if (!$shouldSendRequest) {
                return $this->fallback(null, $jobs);
            }
        } catch (\Exception $e) {
            return $this->fallback($e, $jobs);
        }

        try {
            return $this->makeRequest($jobs);
        } catch (\Exception $e) {
            return $this->fallback($e, $jobs);
        }
    }


    protected function makeRequest($jobs)
    {
        foreach ($this->plugins as $plugin) {
            $plugin->willSendRequest($jobs);
        }

        $response = $this->doRequest($jobs);
        return $this->finalize($response);
    }

    /**
     * @param \WF\Hypernova\JobResult[] $jobResults
     */
    protected function finalize($jobResults) {
        foreach ($jobResults as $jobResult) {
            if ($jobResult->error) {
                foreach ($this->plugins as $plugin) {
                    $plugin->onError($jobResult->error, [$jobResult->originalJob]);
                }
            }
        }

        foreach ($jobResults as $id => $jobResult) {
            if ($jobResult->success) {
                foreach ($this->plugins as $plugin) {
                    $plugin->onSuccess($jobResult);
                }
            }
        }
    }

    /**
     * @param \WF\Hypernova\Job[] $jobs
     *
     * @return \WF\Hypernova\JobResult[]
     */
    protected function doRequest($jobs)
    {
        // overly-clever function that takes an array of Jobs
        // and returns ['foo' => ['name'=> '...', 'data' => [...]], 'bar' => ...]
        $marshalledRequestData = array_reduce(
            array_map(function (Job $job) {
                return $job->jsonSerialize();
            }, $jobs),
            'array_merge',
            []
        );

        $response = $this->getClient()->post($this->url, ['json' => $marshalledRequestData]);

        if ($response->getStatusCode() !== 200) {
            throw new RequestException('Hypernova server returned a non-200 response');
        }

        $body = json_decode($response->getBody());
        if (empty($body->results)) {
            throw new RequestException('Server response missing results');
        }

        if ($body->error) {
            foreach ($this->plugins as $plugin) {
                $plugin->onError($body->error, $body->results);
            }
        }

        $jobResults = [];
        foreach ($body->results as $id => $jobResult) {
            $jobResults[$id] = JobResult::fromServerResult($jobResult, $marshalledRequestData[$id]);
        }
        return $jobResults;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getClient() {
        return new \GuzzleHttp\Client($this->config);
    }

    /**
     * @param mixed $topLevelError
     * @param \WF\Hypernova\Job[] $jobs
     */
    protected function fallback($topLevelError, $jobs)
    {
        $result = new Response();
        $result->error = $topLevelError;
        $result->results = array_map(function (\WF\Hypernova\Job $job) {
            $jobResult = new JobResult();
            $jobResult->html = $this->getFallbackHTML($job->name, $job->data);

            return $jobResult;
        }, $jobs);

        return $result;
    }

    private function getFallbackHTML($moduleName, $data)
    {
        return sprintf(
            '<div data-hypernova-key="%1$s"></div>
    <script type="application/json" data-hypernova-key="%1$s"><!--%2$s--></script>',
            $moduleName,
            json_encode($data)
        );
    }

    /**
     * @return array
     */
    public function createJobs()
    {
        return array_map(function (\WF\Hypernova\Job $job) {
            foreach ($this->plugins as $plugin) {
                try {
                    $job = new Job($job->id, $job->name, $plugin->getViewData($job->name, $job->data));
                } catch (\Exception $e) {
                    $plugin->onError($e, $this->incomingJobs);
                }
            }
            return $job;
        }, $this->incomingJobs);
    }

    /**
     * @param $jobs
     * @return array
     */
    public function prepareRequest($jobs)
    {
        $prepared_jobs = array_map(function ($job) {
            foreach ($this->plugins as $plugin) {
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