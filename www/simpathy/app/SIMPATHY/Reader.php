<?php

namespace App\SIMPATHY;

use App\Models\Job;
use App\SIMPATHY\Exception\ReaderException;

final class Reader
{

    /**
     * @var \App\Models\Job
     */
    private $job;

    /**
     * Reader Constructor
     *
     * @param \App\Models\Job $job
     */
    public function __construct(Job $job)
    {
        if ($job->job_type != Constants::SIMULATION_JOB) {
            throw new ReaderException('Unsupported job type. Only simulation jobs are supported.');
        }
        $this->job = $job;
    }

    /**
     * Read output file and execute an action on the results
     *
     * @param callable $action
     *
     * @return void
     */
    private function reader(callable $action)
    {
        $fp = @fopen($this->job->getData('outputFile'), 'r');
        if (!$fp) {
            throw new ReaderException('Unable to open simpathy output file');
        }
        while (($line = fgets($fp)) !== false) {
            $line = trim($line);
            if (!empty($line) && $line{0} != '#') {
                $fields = explode("\t", $line);
                if (count($fields) == 9 || count($fields) == 10) {
                    call_user_func($action, $fields);
                }
            }
        }
        @fclose($fp);
    }

    /**
     * Read list of pathways contained in the simulation
     *
     * @return \Illuminate\Support\Collection
     */
    public function readPathways()
    {
        $results = [];
        $this->reader(function ($fields) use (&$results) {
            if (!isset($results[$fields[0]])) {
                $results[$fields[0]] = [
                    'id'   => $fields[0],
                    'name' => $fields[1],
                ];
            }
        });
        return collect(array_values($results));
    }

}