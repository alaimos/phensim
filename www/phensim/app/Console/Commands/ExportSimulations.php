<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class ExportSimulations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:simulations {listFile} {outputFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a set of simulations';

    /**
     * Get the content of an optional file
     *
     * @param \App\Models\Job $job
     * @param string          $parameter
     *
     * @return array|null
     */
    public function getOptionalFile(Job $job, string $parameter): ?array
    {
        $file = $job->getParameter($parameter);
        if (empty($file) || !file_exists($file)) {
            return null;
        }

        return [
            'file' => true,
            'name' => basename($file),
            'data' => base64_encode(gzcompress(file_get_contents($file), 9)),
        ];
    }

    /**
     * Build the array containing all the details of a simulation
     *
     * @param \App\Models\Job $simulation
     *
     * @return array
     */
    public function buildSimulationArray(Job $simulation): array
    {
        $parameters = array_merge([], $simulation->job_parameters);
        $parameters['organism'] = $simulation->getParameter('organism');
        $parameters['simulationParameters'] = $simulation->getParameter('simulationParameters');
        $parameters['seed'] = $simulation->getTypedParameter('seed', 'int');
        $parameters['epsilon'] = $simulation->getTypedParameter('epsilon', 'float', 0.00001, false);
        $parameters['nonExpressed'] = $simulation->getTypedParameter('nonExpressed', 'array', [], false);
        $parameters['remove'] = $simulation->getTypedParameter('remove', 'array', [], false);
        $parameters['fdr'] = $simulation->getParameter('fdr', 'QV');
        $parameters['enrichMirs'] = $simulation->getTypedParameter('enrichMirs', 'bool', true, false);
        $parameters['enrichDb'] = $this->getOptionalFile($simulation, 'enrichDb');
        $parameters['nodeTypes'] = $this->getOptionalFile($simulation, 'nodeTypes');
        $parameters['edgeTypes'] = $this->getOptionalFile($simulation, 'edgeTypes');
        $parameters['edgeSubTypes'] = $this->getOptionalFile($simulation, 'edgeSubTypes');
        $parameters['outputFile'] = $this->getOptionalFile($simulation, 'outputFile');
        $parameters['pathwayMatrixOutputFilename'] = $this->getOptionalFile($simulation, 'pathwayOutputFile');
        $parameters['nodesMatrixOutputFilename'] = $this->getOptionalFile($simulation, 'nodesOutputFile');

        return [
            'id'         => $simulation->id,
            'name'       => $simulation->job_name,
            'key'        => $simulation->job_key,
            'status'     => $simulation->job_status,
            'parameters' => $parameters,
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $listFile = $this->argument('listFile');
        $outputFile = $this->argument('outputFile');
        if (!file_exists($listFile) || !is_file($listFile) || !is_readable($listFile)) {
            $this->error('Invalid input list.');

            return 101;
        }
        if (!is_writable(dirname($outputFile))) {
            $this->error('Output file is not writable');

            return 102;
        }
        $list = array_filter(
            array_filter(file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)),
            static function ($e) {
                return is_numeric($e);
            }
        );
        if (empty($list)) {
            $this->error('Input list is empty');

            return 103;
        }

        $result = [];
        foreach ($list as $id) {
            $job = Job::whereId($id)->first();
            if ($job === null) {
                $this->warn('Invalid identifier "' . $id . '".');
            } else {
                $result[] = base64_encode(gzcompress(json_encode($this->buildSimulationArray($job)), 9));
            }
        }

        @file_put_contents($outputFile, json_encode($result));

        if (!file_exists($outputFile)) {
            $this->error('Unable to write output file!');

            return 104;
        }

        return 0;
    }
}
