<?php /** @noinspection DisconnectedForeachInstructionInspection */

namespace App\Console\Commands;

use App\Jobs\DispatcherJob;
use App\Models\Job;
use App\Models\User;
use App\PHENSIM\Constants;
use Illuminate\Console\Command;

class ImportSimulations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:simulations {simulationsArchive} {defaultOwner}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a set of simulations';

    /**
     * Import an optional file
     *
     * @param \App\Models\Job $job
     * @param string          $name
     * @param array|null      $file
     * @param bool            $data
     */
    public function importOptionalFile(Job $job, string $name, ?array $file, bool $data = false): void
    {
        if ($file === null || !isset($file['file']) || !$file['file']) {
            $result = null;
        } else {
            $result = $job->getJobFile($file['name']);
            file_put_contents($result, gzuncompress(base64_decode($file['data'])));
            if (!file_exists($result)) {
                $this->warn('Unable to write "' . $result . '".');
                $result = null;
            }
        }
        if ($data) {
            $job->setData($name, $result);
        } else {
            $job->setParameter($name, $result);
        }
    }

    /**
     * Import a simulation
     *
     * @param array            $simulation
     * @param \App\Models\User $defaultOwner
     */
    public function importSimulationsArray(array $simulation, User $defaultOwner): void
    {
        $parameters = $simulation['parameters'];
        $data = $simulation['data'];
        $sourceId = $parameters['sourceId'] ?? null;
        $job = null;
        if (!empty($sourceId)) {
            $job = Job::whereId($sourceId)->first();
        }
        if ($job === null) {
            $job = new Job();
            $job->job_type = Constants::SIMULATION_JOB;
            $job->job_key = $simulation['key'];
            $owner = User::whereId($simulation['owner'])->first();
            if ($owner === null) {
                $owner = $defaultOwner;
            }
            $job->user_id = $owner->id;
        }
        $job->job_name = $simulation['name'];
        $job->job_status = $simulation['status'];
        $job->job_log = $simulation['log'];
        $job->setParameters($parameters);
        $job->setData($data);
        if (empty($sourceId)) {
            $job->setParameter('sourceId', $simulation['id']);
        }
        $this->importOptionalFile($job, 'enrichDb', $parameters['enrichDb']);
        $this->importOptionalFile($job, 'nodeTypes', $parameters['nodeTypes']);
        $this->importOptionalFile($job, 'edgeTypes', $parameters['edgeTypes']);
        $this->importOptionalFile($job, 'edgeSubTypes', $parameters['edgeSubTypes']);

        $this->importOptionalFile($job, 'outputFile', $parameters['outputFile'], true);
        $this->importOptionalFile($job, 'pathwayMatrixOutputFilename', $parameters['pathwayMatrixOutputFilename'], true);
        $this->importOptionalFile($job, 'nodesMatrixOutputFilename', $parameters['nodesMatrixOutputFilename'], true);
        $job->save();
        if ($simulation['status'] === Job::QUEUED) {
            dispatch(new DispatcherJob($job->id));
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $simulationsArchive = $this->argument('simulationsArchive');
        if (!file_exists($simulationsArchive) || !is_file($simulationsArchive) || !is_readable($simulationsArchive)) {
            $this->error('Invalid input archive.');

            return 101;
        }

        $defaultOwner = User::whereId((int)$this->argument('defaultOwner'))->first();
        if ($defaultOwner === null) {
            $this->error('Invalid default owner.');

            return 102;
        }

        $data = json_decode(file_get_contents($simulationsArchive), true);

        if (empty($data)) {
            $this->error('Input archive is empty');

            return 103;
        }

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $d) {
            $simulationArray = json_decode(gzuncompress(base64_decode($d)), true);
            if (!is_array($simulationArray) || empty($simulationArray)) {
                $this->warn('A corrupted record has been found!');
            } else {
                $this->importSimulationsArray($simulationArray, $defaultOwner);
            }
            $bar->advance();
        }
        $bar->finish();

        $this->info("\nSimulations imported!");

        return 0;
    }
}
