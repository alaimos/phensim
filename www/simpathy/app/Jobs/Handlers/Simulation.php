<?php

namespace App\Jobs\Handlers;


use App\Exceptions\JobException;
use App\Models\Job as JobData;
use App\Models\Organism;
use App\SIMPATHY\Utils;

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
        if (!empty($nodeTypes) && !Utils::checkDbFile($nodeTypes)) {
            throw new JobException('Invalid node types file.');
        }
        $edgeTypes = $this->jobData->getParameter('edgeTypes');
        if (!empty($edgeTypes) && !Utils::checkDbFile($edgeTypes)) {
            throw new JobException('Invalid edge type file.');
        }
        $edgeSubTypes = $this->jobData->getParameter('edgeSubTypes');
        if (!empty($edgeSubTypes) && !Utils::checkDbFile($edgeSubTypes)) {
            throw new JobException('Invalid edge subtype file.');
        }
        $this->log('OK!');
        $this->log('Running SIMPATHY Simulation...', false);

        $this->log('OK!');

        /*$this->jobData->setData([
            'annotationKey'  => $key,
            'annotationFile' => $annotationFile,
        ]);*/
        $this->log('Completed!');


        /*
            'dbFilter'             => null,
            'enrichDb'             => null,
            'nodeTypes'            => null,
            'edgeTypes'            => null,
            'edgeSubTypes'         => null,
        ]
         */
        // TODO: Implement handle() method.
    }
}