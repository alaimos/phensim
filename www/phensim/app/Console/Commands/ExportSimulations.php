<?php

namespace App\Console\Commands;

use App\Models\Simulation;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\ArrayShape;

class ExportSimulations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulations:export {outputFile} {listFile?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a set of simulations';

    /**
     * Get the content of an optional file
     *
     * @param  string|null  $filename
     *
     * @return array|null
     */
    private function getOptionalFile(?string $filename): ?array
    {
        if (empty($filename) || !file_exists($filename)) {
            return null;
        }

        return [
            'file' => true,
            'name' => basename($filename),
            'data' => base64_encode(gzcompress(file_get_contents($filename), 9)),
        ];
    }

    /**
     * Build the array containing all the details of a simulation
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return array
     */
    private function buildSimulationArray(Simulation $simulation): array
    {
        $parameters = array_merge([], $simulation->parameters);
        $parameters['organism'] = $simulation->organism->accession;
        $parameters['seed'] = $simulation->getParameter('seed');
        $parameters['epsilon'] = $simulation->getParameter('epsilon');
        $parameters['remove'] = $simulation->getParameter('remove');
        $parameters['fdr'] = $simulation->getParameter('fdr');
        $parameters['enrichMirs'] = $simulation->getParameter('enrichMiRNAs');
        $parameters['miRNAsEvidence'] = $simulation->getParameter('miRNAsEvidence');
        $parameters['reactome'] = $simulation->getParameter('reactome');
        $parameters['fast'] = $simulation->getParameter('fast');
        $parameters['enrichDb'] = $this->getOptionalFile($simulation->enrichment_database_file);
        $parameters['nodeTypes'] = $this->getOptionalFile($simulation->node_types_file);
        $parameters['edgeTypes'] = $this->getOptionalFile($simulation->edge_types_file);
        $parameters['edgeSubTypes'] = $this->getOptionalFile($simulation->edge_subtypes_file);
        if ($simulation->input_parameters_file !== null) {
            $parameters['simulationParameters'] = $this->getOptionalFile($simulation->input_parameters_file);
        } else {
            $parameters['simulationParameters'] = $simulation->getParameter('simulationParameters');
        }
        if ($simulation->non_expressed_nodes_file !== null) {
            $parameters['nonExpressed'] = $this->getOptionalFile($simulation->non_expressed_nodes_file);
        } else {
            $parameters['nonExpressed'] = $simulation->getParameter('nonExpressed');
        }
        $data = [
            'outputFile'        => $this->getOptionalFile($simulation->output_file),
            'pathwayOutputFile' => $this->getOptionalFile($simulation->pathway_output_file),
            'nodesOutputFile'   => $this->getOptionalFile($simulation->nodes_output_file),
            'zipFile'           => ($simulation->output_file !== null) ? $this->getOptionalFile($simulation->output_file . '.zip') : '',
        ];

        return [
            'id'         => $simulation->id,
            'name'       => $simulation->name,
            'status'     => $simulation->status,
            'log'        => $simulation->logs,
            'owner'      => $simulation->user->email,
            'public'     => $simulation->public,
            'publicKey'  => $simulation->public_key,
            'parameters' => $parameters,
            'data'       => $data,
        ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(): int
    {
        if ($this->hasArgument('listFile')) {
            $listFile = $this->argument('listFile');
        } else {
            $listFile = null;
        }
        $outputFile = $this->argument('outputFile');
        if ($listFile !== null && (!file_exists($listFile) || !is_file($listFile) || !is_readable($listFile))) {
            $this->error('Invalid input list.');

            return 101;
        }
        if (!is_writable(dirname($outputFile))) {
            $this->error('Output file is not writable');

            return 102;
        }
        if ($listFile !== null) {
            $list = array_filter(
                array_filter(file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)),
                static function ($e) {
                    return is_numeric($e);
                }
            );
            $simulationsQuery = Simulation::whereIn('id', $list);
        } else {
            $simulationsQuery = Simulation::query();
        }
        $simulations = $simulationsQuery->with(['user', 'organism'])->get();
        $outputData = [];
        $bar = $this->output->createProgressBar($simulations->count());
        foreach ($simulations as $simulation) {
            $outputData[] = base64_encode(gzcompress(json_encode($this->buildSimulationArray($simulation), JSON_THROW_ON_ERROR), 9));
            $bar->advance();
        }
        $bar->finish();
        if (false === file_put_contents($outputFile, json_encode($outputData, JSON_THROW_ON_ERROR))) {
            $this->error('Unable to write output file!');

            return 104;
        }
        $this->info("\nSimulations exported!");

        return 0;
    }
}
