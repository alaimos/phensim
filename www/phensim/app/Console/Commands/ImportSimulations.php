<?php

namespace App\Console\Commands;

use App\Jobs\SimulationJob;
use App\Models\Organism;
use App\Models\Simulation;
use App\Models\User;
use App\PHENSIM\Launcher;
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
     * A map from old state to new state variables
     */
    private const STATE_MAP = [
        'queued'     => Simulation::QUEUED,
        'processing' => Simulation::PROCESSING,
        'completed'  => Simulation::COMPLETED,
        'failed'     => Simulation::FAILED,
    ];

    /**
     * Import an optional file
     *
     * @param  \App\Models\Simulation  $simulation
     * @param  array|null  $file
     *
     * @return string|null
     * @throws \App\Exceptions\FileSystemException
     */
    public function importOptionalFile(Simulation $simulation, ?array $file): ?string
    {
        if ($file === null || !isset($file['file']) || !$file['file']) {
            $result = null;
        } else {
            $result = $simulation->jobFile($file['name']);
            if (false === file_put_contents($result, gzuncompress(base64_decode($file['data'])))) {
                $this->warn('Unable to write "' . $result . '".');
                $result = null;
            }
        }

        return $result;
    }

    /**
     * Import a simulation. The function has to handle several checks since it might be importing
     * a simulation from an older version of the PHENSIM GUI.
     *
     * @param  array  $imported
     * @param  \App\Models\User  $defaultOwner
     *
     * @throws \App\Exceptions\FileSystemException
     */
    public function importSimulationsArray(array $imported, User $defaultOwner): void
    {
        $parameters = $imported['parameters'];
        $data = $imported['data'];
        $owner = User::where('email', $imported['owner'])->first();
        if ($owner === null) {
            $owner = $defaultOwner;
        }
        $simulation = Simulation::create(
            [
                'name'        => $imported['name'] ?? 'Job of ' . $imported['owner'] . ' imported on ' . now()->toDayDateTimeString(),
                'user_id'     => $owner->id,
                'organism_id' => Organism::where('accession', $parameters['organism'])->firstOrFail()->id,
                'logs'        => $imported['log'],
                'public'      => $imported['public'] ?? false,
                'public_key'  => $imported['publicKey'] ?? null,
                'status'      => is_numeric(
                    $imported['status']
                ) ? $imported['status'] : (self::STATE_MAP[$imported['status']] ?? Simulation::READY),
                'parameters'  => [
                    'epsilon'        => $parameters['epsilon'] ?? 0.001,
                    'seed'           => $parameters['seed'] ?? 0.001,
                    'fdr'            => $parameters['fdr'] ?? Launcher::FDR_BH,
                    'reactome'       => $parameters['reactome'] ?? false,
                    'remove'         => $parameters['remove'] ?? [],
                    'fast'           => $parameters['fast'] ?? [],
                    'enrichMiRNAs'   => $parameters['enrichMirs'] ?? true,
                    'miRNAsEvidence' => $parameters['miRNAsEvidence'] ?? Launcher::EVIDENCE_STRONG,
                ],
            ]
        );
        $simulation->enrichment_database_file = $this->importOptionalFile($simulation, $parameters['enrichDb']);
        $simulation->node_types_file = $this->importOptionalFile($simulation, $parameters['nodeTypes']);
        $simulation->edge_types_file = $this->importOptionalFile($simulation, $parameters['edgeTypes']);
        $simulation->edge_subtypes_file = $this->importOptionalFile($simulation, $parameters['edgeSubTypes']);
        $simulationParameters = $parameters['simulationParameters'] ?? [];
        if (isset($simulationParameters['file']) && $simulationParameters['file']) {
            $simulation->input_parameters_file = $this->importOptionalFile($simulation, $simulationParameters);
        } elseif (isset($simulationParameters[Launcher::OVEREXPRESSION]) || isset($simulationParameters[Launcher::UNDEREXPRESSION])) {
            $simulation->setParameter('simulationParameters', $simulationParameters);
        } else {
            $newParameters = [Launcher::OVEREXPRESSION => [], Launcher::UNDEREXPRESSION => []];
            foreach ($simulationParameters as $parameter => $direction) {
                if (in_array($direction, [Launcher::OVEREXPRESSION, Launcher::UNDEREXPRESSION], true)) {
                    $newParameters[$direction][] = $parameter;
                }
            }
            $simulation->setParameter('simulationParameters', $newParameters);
        }
        $nonExpressedNodes = $parameters['nonExpressed'] ?? null;
        if (is_array($nonExpressedNodes) && isset($nonExpressedNodes['file']) && $nonExpressedNodes['file']) {
            $simulation->non_expressed_nodes_file = $this->importOptionalFile($simulation, $nonExpressedNodes);
        } elseif (is_array($nonExpressedNodes)) {
            $simulation->setParameter('nonExpressed', $nonExpressedNodes);
        }
        $simulation->output_file = $this->importOptionalFile($simulation, $data['outputFile']);
        $simulation->pathway_output_file = $this->importOptionalFile($simulation, $data['pathwayOutputFile']);
        $simulation->nodes_output_file = $this->importOptionalFile($simulation, $data['nodesOutputFile']);
        if (isset($data['zipFile'])) {
            $this->importOptionalFile($simulation, $data['zipFile']);
        }
        $simulation->save();
        if ($simulation->status === Simulation::QUEUED) {
            SimulationJob::dispatch($simulation);
        }
    }


    /**
     * Execute the console command.
     *
     * @return int
     * @throws \JsonException
     * @throws \App\Exceptions\FileSystemException
     */
    public function handle(): int
    {
        $simulationsArchive = $this->argument('simulationsArchive');
        if (!file_exists($simulationsArchive) || !is_file($simulationsArchive) || !is_readable($simulationsArchive)) {
            $this->error('Invalid input archive.');

            return 101;
        }

        $defaultOwner = User::where('id', (int)$this->argument('defaultOwner'))->first();
        if ($defaultOwner === null) {
            $this->error('Invalid default owner.');

            return 102;
        }
        $simulations = json_decode(file_get_contents($simulationsArchive), true, 512, JSON_THROW_ON_ERROR);
        if (empty($simulations)) {
            $this->error('Input archive is empty');

            return 103;
        }
        $bar = $this->output->createProgressBar(count($simulations));
        foreach ($simulations as $simulation) {
            $simulationArray = json_decode(gzuncompress(base64_decode($simulation)), true, 512, JSON_THROW_ON_ERROR);
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
