<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/17/17
 * Time: 12:37 PM
 */

namespace WF\Hypernova\Plugins;

class DevModePlugin extends BasePlugin
{
    public function afterResponse($jobResults)
    {
        return array_map([$this, 'wrapErrors'], $jobResults);
    }

    /**
     * @param \WF\Hypernova\JobResult $jobResult
     *
     * @return string|\WF\Hypernova\JobResult
     */
    protected function wrapErrors(\WF\Hypernova\JobResult $jobResult)
    {
        if (!$jobResult->error) {
            return $jobResult;
        }

        list($message, $formattedStack) = $this->formatError($jobResult->error);

        // technically for purity we should make a new JobResult here rather than mutating the old one, but... eh.
        $jobResult->html = sprintf(
            '<div style="background-color: #ff5a5f; color: #fff; padding: 12px;">
                <p style="margin: 0">
                  <strong>Development Warning!</strong>
                  The <code>%s</code> component failed to render with Hypernova. Error stack:
                </p>
                <ul style="padding: 0 20px">
                    %s
                    %s
                </ul>
            </div>
            %s',
            $jobResult->originalJob->name,
            $message,
            $formattedStack,
            $jobResult->html
        );

        return $jobResult;
    }

    protected function formatError($error)
    {
        return [
            !empty($error['message']) ? '<li><strong>' . $error['message'] . '</strong></li>' : '',
            !empty($error['stack']) ? '<li>' . implode('</li><li>', $error['stack']) . '</li>' : ''
        ];
    }
}
