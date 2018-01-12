<?php

namespace App\PHENSIM;

use App\Models\Job;
use App\Models\Node;
use App\PHENSIM\Exception\ReaderException;
use Illuminate\Support\Collection;

final class Reader
{

    const FIELDS_ALL          = ['pathwayId', 'pathwayName', 'nodeId', 'nodeName', 'isEndpoint', 'isDirectTarget',
                                 'activityScore', 'pValue', 'll', 'targetedBy'];
    const FIELDS_CAST         = [
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
    const LL                  = ['activation', 'inhibition', 'other'];
    const ACTIVATION_COLORING = '%s red,black';
    const INHIBITION_COLORING = '%s blue,yellow';
    const GID_RXP             = '/^[0-9]+$/';

    /**
     * @var \App\Models\Job
     */
    private $job;

    /**
     * Reader Constructor
     *
     * @param \App\Models\Job $job
     *
     * @throws \App\PHENSIM\Exception\ReaderException
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
            return (strtolower($value) == 'yes');
        } elseif (self::FIELDS_CAST[$field] == 'double') {
            return doubleval($value);
        } elseif (self::FIELDS_CAST[$field] == 'll') {
            $tmp = array_map('doubleval', explode(",", $value));
            return array_combine(self::LL, array_slice($tmp, 0, 3));
        } elseif (self::FIELDS_CAST[$field] == 'array') {
            return (empty($value)) ? [] : explode(",", $value);
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
        if ($n == 9) $fields[] = '';
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
     * @throws \App\PHENSIM\Exception\ReaderException
     *
     * @return void
     */
    private function reader(callable $action)
    {
        $fp = @fopen($this->job->getData('outputFile'), 'r');
        if (!$fp) throw new ReaderException('Unable to open phensim output file');
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
     * Returns the filename of SIMPATHY output file
     *
     * @return string
     */
    public function getOutputFilename(): string
    {
        return (string)$this->job->getData('outputFile');
    }

    /**
     * Read list of pathways contained in the simulation
     *
     * @return \Illuminate\Support\Collection
     */
    public function readPathwaysList(): Collection
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
            if ($fields['isDirectTarget']) {
                $results[$pid]['directTargets']++;
            }
            if ($fields['activityScore'] > 0) {
                $results[$pid]['activatedNodes']++;
            }
            if ($fields['activityScore'] < 0) {
                $results[$pid]['inhibitedNodes']++;
            }
        });
        return collect(array_values($results));
    }

    /**
     * Read the list of altered genes for a single pathway
     *
     * @param string        $pathway
     * @param callable|null $callback
     *
     * @return \Illuminate\Support\Collection
     */
    public function readPathway(string $pathway, callable $callback = null): Collection
    {
        $results = [];
        $this->reader(function ($fields) use (&$results, $pathway, $callback) {
            if ($fields['pathwayId'] == $pathway && $fields['activityScore'] != 0.0) {
                if ($callback !== null) {
                    $tmp = call_user_func($callback, $fields);
                    if ($tmp !== null) {
                        $results[] = $tmp;
                    }
                } else {
                    $results[] = $fields;
                }
            }
        });
        return collect($results);
    }

    /**
     * Returns parameters for pathway coloring kegg link
     *
     * @param string $pathway
     *
     * @return array
     */
    public function makePathwayColoring(string $pathway): array
    {
        $mapId        = str_ireplace('path:', '', $pathway);
        $coloringData = $this->readPathway($pathway, function (array $data) {
            /** @var Node $node */
            $node = Node::whereAccession($data['nodeId'])->first();
            if ($node !== null && $node->type != 'mirna') {
                if ($data['activityScore'] > 0) {
                    return sprintf(self::ACTIVATION_COLORING, $node->accession);
                } elseif ($data['activityScore'] < 0) {
                    return sprintf(self::INHIBITION_COLORING, $node->accession);
                }
            }
            return null;
        });
        return [
            'mapId'    => $mapId,
            'coloring' => $coloringData->implode(PHP_EOL),
        ];
    }

}