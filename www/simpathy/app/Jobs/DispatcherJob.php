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
            $jobData = JobData::whereId($this->jobDataId)->first();
            $class = '\App\Jobs\Handlers\\' . studly_case($jobData->job_type);
            if (!class_exists($class)) {
                $this->fail(new JobException('Job handler (' . $class . ') not found.'));
            } else {
                $jobData->job_status = JobData::PROCESSING;
                $jobData->save();
                /** @var AbstractHandler $handler */
                $handler = new $class($jobData);
                $handler->handle();
                $jobData->job_status = JobData::COMPLETED;
                $jobData->save();
            }
        } catch (\Exception $e) {
            if ($jobData instanceof JobData) {
                $jobData->job_status = JobData::FAILED;
                $jobData->appendLog("Error!\nAn exception occurred during execution: " . $e->getMessage());
            }
            $this->fail($e);
        }
    }
}
