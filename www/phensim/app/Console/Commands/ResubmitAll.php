<?php

namespace App\Console\Commands;

use App\Jobs\DispatcherJob;
use App\Models\Job;
use Illuminate\Console\Command;

class ResubmitAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resubmit:all {--failed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resubmit simulation';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $query = Job::query();
        if ($this->option('failed')) {
            $query->where('job_status', '=', Job::FAILED);
        }
        foreach ($query->get() as $job) {
            /** @var \App\Models\Job $job */
            $this->info("Dispatching job \"" . $job->getJobName() . "\" (" . $job->id . ") of " . $job->user->name);
            \Auth::login($job->user, false);
            $job->job_status = Job::QUEUED;
            $job->deleteJobDirectory();
            $job->job_data = [];
            $job->save();
            dispatch(new DispatcherJob($job->id));
            \Auth::logout();
        }
        return 0;
    }
}
