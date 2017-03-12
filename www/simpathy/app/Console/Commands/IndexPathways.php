<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IndexPathways extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:pathways';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes pathways for a faster execution of other commands';

    /**
     * Create a new command instance.
     *
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
        $this->info("Running MITHrIL 2 index command");
        $m2 = resource_path('bin/MITHrIL2.jar');
        $command = 'java -jar ' . escapeshellarg($m2) . ' index -verbose -organism hsa -enrichment-evidence-type STRONG';
        $return = null;
        passthru($command, $return);
        if ($return == 0) {
            $this->info("Done!");
        } else {
            $this->error("An error occurred!");
        }
        return $return;
    }
}
