<?php

namespace App\Console\Commands;

use App\Models\Organism;
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
     * @return int
     */
    public function handle()
    {
        $m2 = resource_path('bin/MITHrIL2.jar');
        $command = '/opt/jdk/bin/java -jar ' . escapeshellarg($m2) . ' index -verbose -organism %s -enrichment-evidence-type STRONG';
        foreach (Organism::all() as $organism) {
            $cmd = sprintf($command, escapeshellarg($organism->accession));
            $this->info("Indexing " . $organism->name . " pathways.");
            $return = null;
            passthru($cmd, $return);
            if ($return == 0) {
                $this->info("Done!");
            } else {
                $this->error("An error occurred!");
                return 105;
            }
        }
        return 0;
    }
}
