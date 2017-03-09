<?php

namespace App\Jobs\Handlers;

use App\Models\Job as JobData;

interface HandlerInterface
{

    /**
     * Checks if this class can handle a specific job
     *
     * @param JobData $jobData
     *
     * @return boolean
     */
    public function canHandleJob(JobData $jobData);

    /**
     * Execute the job.
     *
     * @throws \App\Exceptions\JobException
     * @return void
     */
    public function handle();

}
