<?php

namespace App\Jobs;

use App\Exceptions\ProcessingJobException;
use App\Models\Simulation;
use App\PHENSIM\Launcher;
use App\PHENSIM\Reader;
use App\PHENSIM\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SimulationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * A simulation associated to this job
     *
     * @var \App\Models\Simulation
     */
    protected Simulation $simulation;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Simulation  $simulation
     */
    public function __construct(Simulation $simulation)
    {
        $this->simulation = $simulation;
    }

    /**
     * Checks if the provided input parameters contains at least one node
     *
     * @return bool
     */
    private function checkSimulationParameters(): bool
    {
        $parameters = $this->simulation->getParameter('inputParameters');
        $over = $parameters[Launcher::OVEREXPRESSION] ?? [];
        $under = $parameters[Launcher::UNDEREXPRESSION] ?? [];

        return (count($over) > 0 || count($under) > 0);
    }

    /**
     * Checks all job parameters and throws a ProcessingJobException if some errors are detected
     *
     * @throws \App\Exceptions\ProcessingJobException
     */
    private function checkAllParameters(): void
    {
        $this->simulation->appendLog('Checking parameters...', false);
        if ($this->simulation->input_parameters_file === null && !$this->checkSimulationParameters()) {
            throw new ProcessingJobException('No valid simulation parameters provided.');
        }
        if ($this->simulation->enrichment_database_file !== null && !Utils::checkDbFile(
                $this->simulation->enrichment_database_file
            )) {
            throw new ProcessingJobException('An invalid enrichment database has been provided.');
        }
        if ($this->simulation->node_types_file && !Utils::checkNodeTypeFile($this->simulation->node_types_file)) {
            throw new ProcessingJobException('An invalid node types file has been provided.');
        }
        if ($this->simulation->edge_types_file && !Utils::checkEdgeTypeFile($this->simulation->edge_types_file)) {
            throw new ProcessingJobException('An invalid edge type file has been provided.');
        }
        if ($this->simulation->edge_subtypes_file && !Utils::checkEdgeSubTypeFile(
                $this->simulation->edge_subtypes_file
            )) {
            throw new ProcessingJobException('An invalid edge subtype file has been provided.');
        }
        $this->simulation->appendLog("OK!");
    }

    /**
     * Prepare the phensim simulation launcher
     *
     * @return \App\PHENSIM\Launcher
     * @throws \App\Exceptions\FileSystemException
     */
    private function prepareSimulationLauncher(): Launcher
    {
        $this->simulation->appendLog('Preparing simulation...', false);
        $launcher = new Launcher($this->simulation->jobDirectory());
        $launcher->setOrganism($this->simulation->organism->accession)
                 ->setEpsilon($this->simulation->getParameter('epsilon', 0.001))
                 ->setSeed($this->simulation->getParameter('seed'))
                 ->setRemoveNodes($this->simulation->getParameter('remove', []))
                 ->setFdrMethod($this->simulation->getParameter('fdr', Launcher::FDR_BH))
                 ->setReactome($this->simulation->getParameter('reactome') === true)
                 ->setFast($this->simulation->getParameter('fast') === true);
        if ($this->simulation->getParameter('enrichMiRNAs', false)) {
            $launcher->addEnricher(Launcher::MIRNA_ENRICHER);
            $launcher->setMiRNAEnrichmentEvidence(
                $this->simulation->getParameter('miRNAsEvidence', Launcher::EVIDENCE_STRONG)
            );
        }
        if ($this->simulation->enrichment_database_file !== null) {
            $launcher->setDBEnricher(
                $this->simulation->enrichment_database_file,
                $this->simulation->getParameter('filter'),
                $this->simulation->node_types_file,
                $this->simulation->edge_types_file,
                $this->simulation->edge_subtypes_file
            );
        }
        if ($this->simulation->input_parameters_file === null) {
            $parameters = $this->simulation->getParameter('inputParameters');
            $over = $parameters[Launcher::OVEREXPRESSION] ?? [];
            $under = $parameters[Launcher::UNDEREXPRESSION] ?? [];
            $launcher->buildInputFile($over, $under);
        } else {
            $launcher->setInputParametersFilePath($this->simulation->input_parameters_file);
        }
        if ($this->simulation->non_expressed_nodes_file === null) {
            $nonExpr = $this->simulation->getParameter('nonExpressed');
            if (is_array($nonExpr) && !empty($nonExpr)) {
                $launcher->buildNonExpressedFile($nonExpr);
            }
        } else {
            $launcher->setNonExpressedNodesFilePath($this->simulation->non_expressed_nodes_file);
        }
        $this->simulation->appendLog("OK!");

        return $launcher;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Delete this job from the queue. If the job fails no other attempts should be made
            $this->delete();
            if (in_array($this->simulation->status, [Simulation::PROCESSING, Simulation::COMPLETED], true)) {
                // This job has been completed or is being processed! Why am I still trying to run it?
                return;
            }
            $this->simulation->update(['logs' => '', 'status' => Simulation::PROCESSING]);
            $this->simulation->signalToCallback();
            $this->checkAllParameters();
            $launcher = $this->prepareSimulationLauncher();
            $this->simulation->appendLog('Running PHENSIM Simulation...');
            $i = 0;
            $launcher->run(
                function ($type, $buffer) use (&$i) {
                    $this->simulation->appendLog($buffer, false);
                    if (($i++ % 100) === 0) {
                        $this->simulation->signalToCallback();
                    }
                }
            );
            $this->simulation->appendLog('Processing PHENSIM Results...');
            new Reader($launcher->getOutputFilename());
            $this->simulation->appendLog('Saving...');
            $this->simulation->fill(
                [
                    'output_file' => $launcher->getOutputFilename(),
                    'pathway_output_file' => $launcher->getPathwayMatrixOutputFilename(),
                    'nodes_output_file' => $launcher->getNodesMatrixOutputFilename(),
                    'sbml_output_file' => $launcher->getSbmlOutputFilename(),
                    'sif_output_file' => $launcher->getSifOutputFilename(),
                    'status' => Simulation::COMPLETED,
                ]
            );
            $this->simulation->appendLog('Completed!');
            $this->simulation->signalToCallback();
        } catch (Throwable $e) {
            $this->simulation->status = Simulation::FAILED;
            $this->simulation->appendLog("\nAn error occurred: ".$e->getMessage());
            $this->simulation->signalToCallback();
            $this->fail($e);
        }
    }
}
