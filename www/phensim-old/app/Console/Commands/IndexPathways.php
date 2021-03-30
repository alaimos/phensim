<?php

namespace App\Console\Commands;

use App\Models\Organism;
use App\PHENSIM\Utils;
use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $command = [
            env('JAVA_PATH') . '/java',
            '-jar',
            resource_path('bin/MITHrIL2.jar'),
            'index',
            '-verbose',
            '-organism',
            '',
            '-enrichment-evidence-type',
            'STRONG',
        ];
        foreach (Organism::all() as $organism) {
            $command[6] = $organism->accession;
            $this->info("Indexing " . $command[6] . " pathways.");
            try {
                Utils::runCommand(
                    $command,
                    null,
                    null,
                    function ($type, $buffer) {
                        if ($type === Process::OUT) {
                            $this->info($buffer);
                        } else {
                            $this->error($buffer);
                        }
                    }
                );
                $this->info("Done!");
            } catch (ProcessFailedException $e) {
                $this->error("An error occurred!");

                return 105;
            }
        }

        return 0;
    }
}
