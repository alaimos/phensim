<?php

namespace App\Jobs\Handlers;


use App\Exceptions\JobException;
use App\Models\Job as JobData;
use App\Models\Organism;
use App\PHENSIM\Launcher;
use App\PHENSIM\Utils;

class Simulation extends AbstractHandler
{

    /**
     * Checks if this class can handle a specific job
     *
     * @param JobData $jobData
     *
     * @return boolean
     */
    public function canHandleJob(JobData $jobData)
    {
        return strtolower($jobData->job_type) == 'simulation';
    }

    /**
     * Execute the job.
     *
     * @throws \App\Exceptions\JobException
     * @return void
     */
    public function handle()
    {
        $this->log('Checking parameters...', false);
        /** @var Organism $organism */
        $organism = Organism::whereAccession($this->jobData->getParameter('organism'))->first();
        if ($organism === null) {
            throw new JobException('Invalid organism.');
        }
        $simulationParameters = $this->jobData->getParameter('simulationParameters');
        if (!Utils::checkSimulationParameters($simulationParameters)) {
            throw new JobException('Invalid simulation parameters.');
        }
        $enrichDb = $this->jobData->getParameter('enrichDb');
        if (!empty($enrichDb) && !Utils::checkDbFile($enrichDb)) {
            throw new JobException('Invalid enrichment database.');
        }
        $nodeTypes = $this->jobData->getParameter('nodeTypes');
        if (!empty($nodeTypes) && !Utils::checkNodeTypeFile($nodeTypes)) {
            throw new JobException('Invalid node types file.');
        }
        $edgeTypes = $this->jobData->getParameter('edgeTypes');
        if (!empty($edgeTypes) && !Utils::checkEdgeTypeFile($edgeTypes)) {
            throw new JobException('Invalid edge type file.');
        }
        $edgeSubTypes = $this->jobData->getParameter('edgeSubTypes');
        if (!empty($edgeSubTypes) && !Utils::checkEdgeSubTypeFile($edgeSubTypes)) {
            throw new JobException('Invalid edge subtype file.');
        }
        $this->log('OK!');
        $this->log('Running SIMPATHY Simulation...', false);
        $launcher = new Launcher($this->jobData);
        $launcher
            ->setOrganism($organism->accession)
            ->setSeed($this->jobData->getTypedParameter('seed', 'int'))
            ->setEpsilon($this->jobData->getTypedParameter('epsilon', 'float', 0.001, false))
            ->setSimulationParameters($simulationParameters)
            ->setNonExpressedNodes($this->jobData->getTypedParameter('nonExpressed', 'array', [], false));
        $mirs = $this->jobData->getTypedParameter('enrichMirs', 'bool', true, false);
        if ($mirs) {
            $launcher->addEnricher('mirna');
        }
        if (!empty($enrichDb)) {
            $launcher->addEnricher('textEnricher');
            $launcher->addEnricherParameters('inputFile', $enrichDb);
            $filter = $this->jobData->getParameter('dbFilter');
            if (!empty($filter)) {
                $launcher->addEnricherParameters('filter', $filter);
            }
            if (!empty($nodeTypes)) {
                $launcher->addEnricherParameters('nodeTypesFile', $nodeTypes);
            }
            if (!empty($edgeTypes)) {
                $launcher->addEnricherParameters('edgeTypesFile', $edgeTypes);
            }
            if (!empty($edgeSubTypes)) {
                $launcher->addEnricherParameters('edgeSubTypesFile', $edgeSubTypes);
            }
        }
        try {
            $result = $launcher->run();
            $this->log('OK!');
            $this->log(implode(PHP_EOL, $result));
        } catch (\Exception $e) {
            throw new JobException($e->getMessage(), 0, $e);
        }
        $this->jobData->setData([
            'outputFile' => $launcher->getOutputFilename(),
        ]);
        $this->log('Completed!');
    }
}