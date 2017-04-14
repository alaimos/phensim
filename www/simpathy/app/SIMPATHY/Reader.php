<?php

namespace App\SIMPATHY;

use App\Models\Job;
use App\SIMPATHY\Exception\ReaderException;

final class Reader
{

    const FIELDS_ALL  = ['pathwayId', 'pathwayName', 'nodeId', 'nodeName', 'isEndpoint', 'isDirectTarget',
                         'activityScore', 'pValue', 'll', 'targetedBy'];
    const FIELDS_CAST = [
        'pathwayId'      => null,
        'pathwayName'    => 'pathway',
        'nodeId'         => null,
        'nodeName'       => null,
        'isEndpoint'     => 'boolean',
        'isDirectTarget' => 'boolean',
        'activityScore'  => 'double',
        'pValue'         => 'double',
        'll'             => 'll',
        'targetedBy'     => 'array',
    ];
    const LL          = ['activation', 'inhibition', 'other'];

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
     * Modifies a file to use its data for subsequent analysis
     *
     * @param string $field
     * @param string $value
     *
     * @return array|bool|float|mixed|string
     */
    private function cast(string $field, string $value)
    {
        if (self::FIELDS_CAST[$field] == 'boolean') {
            return (strtolower($value) == 'no');
        } elseif (self::FIELDS_CAST[$field] == 'double') {
            return doubleval($value);
        } elseif (self::FIELDS_CAST[$field] == 'll') {
            $tmp = array_map('doubleval', explode(",", $value));
            return array_combine(self::LL, array_slice($tmp, 0, 3));
        } elseif (self::FIELDS_CAST[$field] == 'array') {
            if (empty($value)) return [];
            return explode(",", $value);
        } elseif (self::FIELDS_CAST[$field] == 'pathway') {
            return preg_replace('/\s+\-\s+enriched/i', '', $value);
        }
        return $value;
    }

    /**
     * Prepares all fields in a line of SIMPATHY output file
     *
     * @param array $fields
     *
     * @return array
     */
    private function prepare(array $fields)
    {
        $n = count($fields);
        if ($n == 9) {
            $fields[] = '';
        }
        $fields = array_combine(self::FIELDS_ALL, $fields);
        array_walk($fields, function (&$value, $key) {
            $value = $this->cast($key, $value);
        });
        return $fields;
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
                    call_user_func($action, $this->prepare($fields));
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
            $pid = $fields['pathwayId'];
            if (!isset($results[$pid])) {
                $results[$pid] = [
                    'id'             => $pid,
                    'name'           => $fields['pathwayName'],
                    'directTargets'  => 0,
                    'activatedNodes' => 0,
                    'inhibitedNodes' => 0,
                ];
            }
            if ($fields['isDirectTarget']) $results[$pid]['directTargets']++;
            if ($fields['activityScore'] > 0) $results[$pid]['activatedNodes']++;
            if ($fields['activityScore'] < 0) $results[$pid]['inhibitedNodes']++;
        });
        return collect(array_values($results));
    }

}