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
     * @var array default configuration that will be passed to HTTP client
     */
    protected static $configDefaults = [];

    /**
     * @var \WF\Hypernova\Plugins\Plugin[]
     */
    protected $plugins = [];

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
    protected $incomingJobs = [];

    /**
     * Renderer constructor.
     *
     * @param string $url
     * @param array $plugins
     * @param array $config
     */
    public function __construct($url, $plugins = [], $config = [])
    {
        $this->url = $url;
        $this->plugins = $plugins;
        $this->config = array_merge(static::$configDefaults, $config);
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
     * @param string $id
     * @param \WF\Hypernova\Job|array $job Job to add, { [view]: { name: String, data: ReactProps } }
     */
    public function addJob($id, $job)
    {
        if (is_array($job)) {
            $job = Job::fromArray($job);
        }
        $this->incomingJobs[$id] = $job;
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

    /**
     * @param \WF\Hypernova\Job[] $jobs
     *
     * @return \WF\Hypernova\JobResult[]
     */
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
     *
     * @return \WF\Hypernova\Response
     */
    protected function finalize($jobResults)
    {
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

        foreach ($this->plugins as $plugin) {
            $jobResults = $plugin->afterResponse($jobResults);
        }

        $response = new Response();
        $response->results = $jobResults;

        return $response;
    }

    /**
     * @param \WF\Hypernova\Job[] $jobs
     *
     * @return \WF\Hypernova\JobResult[]
     * @throws \Exception
     */
    protected function doRequest($jobs)
    {
        $response = $this->getClient()->post($this->url, ['json' => $jobs]);

        $body = json_decode($response->getBody(), true);
        if (empty($body['results'])) {
            throw new \Exception('Server response missing results');
        }

        if ($body['error']) {
            foreach ($this->plugins as $plugin) {
                $plugin->onError($body['error'], isset($body['results']) ? $body['results'] : null);
            }
        }

        $jobResults = [];
        foreach ($body['results'] as $id => $jobResult) {
            $jobResults[$id] = JobResult::fromServerResult($jobResult, $this->incomingJobs[$id]);
        }
        return $jobResults;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        return new \GuzzleHttp\Client($this->config);
    }

    /**
     * @param mixed $topLevelError
     * @param \WF\Hypernova\Job[] $jobs
     *
     * @return \WF\Hypernova\Response
     */
    protected function fallback($topLevelError, $jobs)
    {
        $result = new Response();
        $result->error = $topLevelError;
        $result->results = array_map(function (\WF\Hypernova\Job $job) {
            $jobResult = new JobResult();
            $uuid = \Ramsey\Uuid\Uuid::uuid4();
            $jobResult->html = $this->getFallbackHTML($job->name, $job->data, $uuid);
            $jobResult->meta = ['uuid' => (string) $uuid];
            $jobResult->originalJob = $job;

            return $jobResult;
        }, $jobs);

        return $result;
    }

    /**
     * @param string $moduleName
     * @param array $data
     * @param \Ramsey\Uuid\UuidInterface $uuid
     *
     * @return string
     */
    protected function getFallbackHTML($moduleName, $data, $uuid)
    {
        return sprintf(
            '<div data-hypernova-key="%1$s" data-hypernova-id="%2$s"></div>
    <script type="application/json" data-hypernova-key="%1$s" data-hypernova-id="%2$s"><!--%3$s--></script>',
            $moduleName,
            $uuid,
            json_encode($data)
        );
    }

    /**
     * @return \WF\Hypernova\Job[]
     */
    protected function createJobs()
    {
        return array_map(function (\WF\Hypernova\Job $job) {
            foreach ($this->plugins as $plugin) {
                try {
                    $job = new Job($job->name, $plugin->getViewData($job->name, $job->data), $job->metadata);
                } catch (\Exception $e) {
                    $plugin->onError($e, $this->incomingJobs);
                }
            }
            return $job;
        }, $this->incomingJobs);
    }

    /**
     * Prepare Request
     *
     * @param array $jobs Jobs
     *
     * @return array
     */
    protected function prepareRequest($jobs)
    {
        $preparedJobs = $jobs;
        foreach ($this->plugins as $plugin) {
            // Pass both jobs we are working with an original, incoming jobs so
            // that every plugin has a chance to see _all_ original jobs.
            $preparedJobs = $plugin->prepareRequest($preparedJobs, $jobs);
        }

        $shouldSend = true;
        foreach ($this->plugins as $plugin) {
            $shouldSend = $shouldSend && $plugin->shouldSendRequest($preparedJobs);
        }

        return [$shouldSend, $preparedJobs];
    }
}
