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
    protected $signature = 'resubmit:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resubmit all simulation';

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
        foreach (Job::all() as $job) {
            $this->info("Dispatching job \"" . $job->getJobName() . "\" (" . $job->id . ") of " . $job->user->name);
            dispatch(new DispatcherJob($job->id));
        }
        return 0;
    }
}
