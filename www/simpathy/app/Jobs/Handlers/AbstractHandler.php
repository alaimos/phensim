<?php

namespace App\Jobs\Handlers;

use App\Exceptions\CommandException;
use App\Exceptions\JobException;
use App\SIMPATHY\Utils;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Job as JobData;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var \App\Models\Job
     */
    protected $jobData;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Job $jobData
     */
    public function __construct($jobData)
    {
        $this->setJobData($jobData);
    }


    /**
     * Get the current job object
     *
     * @return JobData
     */
    public function getJobData()
    {
        return $this->jobData;
    }

    /**
     * Set the current job object
     *
     * @param JobData $jobData
     * @return $this
     */
    public function setJobData($jobData)
    {
        if (!$this->canHandleJob($jobData)) {
            throw new JobException('This handler (' . get_class($this) . ') cannot handle a job of type ' . $jobData->job_type . '.');
        }
        $this->jobData = $jobData;
        return $this;
    }

    /**
     * Map command exception to message
     *
     * @param string           $command
     * @param CommandException $e
     * @param array            $errorCodeMap
     * @return boolean
     * @throws JobException
     */
    protected function mapCommandException($command, CommandException $e, array $errorCodeMap = [])
    {
        $code = intval($e->getMessage());
        if (isset($errorCodeMap[$code])) {
            throw new JobException($errorCodeMap[$code]);
        } else {
            throw new JobException('Execution of command "' . $command . '" returned error code ' . $code . '.');
        }
    }

    /**
     * Runs a shell command and checks for successful completion of execution
     *
     * @param string     $command
     * @param array|null $output
     * @param array      $errorCodeMap
     * @return boolean
     * @throws JobException
     */
    protected function runCommand($command, array &$output = null, array $errorCodeMap = [])
    {
        try {
            return Utils::runCommand($command, $output);
        } catch (CommandException $e) {
            return $this->mapCommandException($command, $e, $errorCodeMap);
        }
    }

    /**
     * Append text to the log
     *
     * @param string  $text
     * @param boolean $appendNewLine
     * @return $this
     */
    public function log($text, $appendNewLine = true)
    {
        $this->jobData->appendLog($text, $appendNewLine);
        return $this;
    }

}
