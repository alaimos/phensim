<?php

namespace App\Jobs;

use App\Exceptions\JobException;
use App\Jobs\Handlers\AbstractHandler;
use App\Models\Job as JobData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatcherJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $jobDataId;

    /**
     * Create a new job instance.
     *
     * @param int $jobDataId
     *
     * @throws \App\Exceptions\JobException
     */
    public function __construct(int $jobDataId)
    {
        if (JobData::find($jobDataId) === null) {
            throw new JobException('The identifier provided to the job dispatcher is invalid.');
        }
        $this->jobDataId = $jobDataId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobData = null;
        try {
            /** @var JobData $jobData */
            $jobData = JobData::whereId($this->jobDataId)->first();
            if (in_array($jobData->job_status, [JobData::PROCESSING, JobData::COMPLETED], true)) {
                // job is being processed (or has been processed) by another job.
                return;
            }
            \Auth::login($jobData->user, false);
            $class = '\App\Jobs\Handlers\\' . studly_case($jobData->job_type);
            if (!class_exists($class)) {
                $this->fail(new JobException('Job handler (' . $class . ') not found.'));
            } else {
                $jobData->job_log = '';
                $jobData->job_status = JobData::PROCESSING;
                $jobData->save();
                /** @var AbstractHandler $handler */
                $handler = new $class($jobData);
                $handler->handle();
                $jobData->job_status = JobData::COMPLETED;
                $jobData->save();
            }
            \Auth::logout();
        } catch (\Exception $e) {
            if ($jobData instanceof JobData) {
                $jobData->job_status = JobData::FAILED;
                $jobData->appendLog("Error!\nAn exception occurred during execution: " . $e->getMessage());
            }
            if (\Auth::id() !== null) {
                \Auth::logout();
            }
            $this->fail($e);
        }
    }
}