<?php

namespace App\Console\Commands;

use App\Jobs\DispatcherJob;
use App\Models\Job;
use Illuminate\Console\Command;

class ResubmitOne extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resubmit:one {job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resubmit one simulation';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = (int)$this->argument('job');
        $job = Job::whereId($id)->first();
        if ($job !== null) {
            $this->info("Dispatching job \"" . $job->getJobName() . "\" (" . $job->id . ") of " . $job->user->name);
            \Auth::login($job->user, false);
            $job->job_status = Job::QUEUED;
            $job->job_log = '';
            $job->job_data = [];
            $job->save();
            dispatch(new DispatcherJob($job->id));
            \Auth::logout();
        } else {
            $this->error('Job ' . $id . ' not found!!');
        }
        return 0;
    }
}
