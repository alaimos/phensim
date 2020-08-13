<?php

namespace App\PHENSIM;

use App\Models\Job;
use App\Models\Node;
use App\PHENSIM\Exception\ReaderException;
use Illuminate\Support\Collection;

final class Reader
{

    public const FIELDS_ALL          = [
        'pathwayId',
        'pathwayName',
        'nodeId',
        'nodeName',
        'isEndpoint',
        'isDirectTarget',
        'activityScore',
        'pValue',
        'FDR',
        'LL',
        'pathwayActivityScore',
        'pathwayPValue',
        'pathwayFDR',
        'pathwayLL',
        'targetedBy',
    ];
    public const FIELDS_CAST         = [
        'pathwayId'            => null,
        'pathwayName'          => 'pathway',
        'nodeId'               => null,
        'nodeName'             => null,
        'isEndpoint'           => 'boolean',
        'isDirectTarget'       => 'boolean',
        'activityScore'        => 'double',
        'pValue'               => 'double',
        'FDR'                  => 'double',
        'LL'                   => 'll',
        'pathwayActivityScore' => 'double',
        'pathwayPValue'        => 'double',
        'pathwayFDR'           => 'double',
        'pathwayLL'            => 'll',
        'targetedBy'           => 'array',
    ];
    public const LL                  = ['activation', 'inhibition', 'other'];
    public const ACTIVATION_COLORING = '%s red,black';
    public const INHIBITION_COLORING = '%s blue,yellow';
    public const GID_RXP             = '/^[0-9]+$/';

    /**
     * @var string
     */
    private $outputFile;

    /**
     * @var string
     */
    private $pathwayMatrixOutputFilename;

    /**
     * @var string
     */
    private $nodesMatrixOutputFilename;

    /**
     * Reader Constructor
     *
     * @param \App\Models\Job|string $job
     *
     * @throws \App\PHENSIM\Exception\ReaderException
     */
    public function __construct($job)
    {
        if ($job instanceof Job) {
            if ($job->job_type !== Constants::SIMULATION_JOB) {
                throw new ReaderException('Unsupported job type. Only simulation jobs are supported.');
            }
            $this->outputFile = $job->getData('outputFile');
            $this->pathwayMatrixOutputFilename = $job->getData('pathwayOutputFile');
            $this->nodesMatrixOutputFilename = $job->getData('nodesOutputFile');
        } elseif (file_exists($job)) {
            $this->outputFile = $job;
        } else {
            throw new ReaderException('Unsupported input.');
        }
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
        if (self::FIELDS_CAST[$field] === 'boolean') {
            return (strtolower($value) === 'yes');
        }
        if (self::FIELDS_CAST[$field] === 'double') {
            return (float)$value;
        }
        if (self::FIELDS_CAST[$field] === 'll') {
            $tmp = array_map('doubleval', explode(",", $value));
            $tmp = array_slice($tmp, 0, 3);
            if (count($tmp) < 3) {
                $tmp = [null, null, null];
            }

            return array_combine(self::LL, $tmp);
        }
        if (self::FIELDS_CAST[$field] === 'array') {
            return (empty($value)) ? [] : explode(",", $value);
        }
        if (self::FIELDS_CAST[$field] === 'pathway') {
            return preg_replace('/\s+-\s+enriched/i', '', $value);
        }

        return $value;
    }

    /**
     * Prepares all fields in a line of PHENSIM output file
     *
     * @param array $fields
     *
     * @return array
     */
    private function prepare(array $fields): array
    {
        $n = count($fields);
        while ($n < count(self::FIELDS_ALL)) {
            $fields[] = '';
            $n = count($fields);
        }
        $fields = array_combine(self::FIELDS_ALL, $fields);
        array_walk(
            $fields,
            function (&$value, $key) {
                $value = $this->cast($key, $value);
            }
        );

        return $fields;
    }

    /**
     * Read output file and execute an action on the results
     *
     * @param callable $action
     *
     * @return void
     * @throws \App\PHENSIM\Exception\ReaderException
     *
     */
    private function reader(callable $action): void
    {
        $fp = @fopen($this->outputFile, 'rb');
        if (!$fp) {
            throw new ReaderException('Unable to open phensim output file');
        }
        while (($line = fgets($fp)) !== false) {
            $line = trim($line);
            if (!empty($line) && $line{0} !== '#') {
                $fields = explode("\t", $line);
                $n = count($fields);
                if ($n >= 12 && $n <= 14) {
                    $action($this->prepare($fields));
                }
            }
        }
        @fclose($fp);
    }

    /**
     * Returns the filename of PHENSIM output
     *
     * @return string
     */
    public function getOutputFilename(): string
    {
        return (string)$this->outputFile;
    }

    /**
     * Returns the filename of PHENSIM pathway matrix
     *
     * @return string
     */
    public function getPathwayMatrixOutputFilename(): string
    {
        return $this->pathwayMatrixOutputFilename;
    }

    /**
     * Returns the filename of PHENSIM nodes matrix
     *
     * @return string
     */
    public function getNodesMatrixOutputFilename(): string
    {
        return $this->nodesMatrixOutputFilename;
    }


    /**
     * Read list of pathways contained in the simulation
     *
     * @return \Illuminate\Support\Collection
     */
    public function readPathwaysList(): Collection
    {
        $results = [];
        $this->reader(
            static function ($fields) use (&$results) {
                $pid = $fields['pathwayId'];
                if (!isset($results[$pid])) {
                    $results[$pid] = [
                        'id'            => $pid,
                        'name'          => $fields['pathwayName'],
                        'activityScore' => $fields['pathwayActivityScore'],
                        'pValue'        => $fields['pathwayPValue'],
                        'FDR'           => $fields['pathwayFDR'],
                        'LL'            => $fields['pathwayLL'],
                    ];
                }
            }
        );

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
    public function readPathway(string $pathway, ?callable $callback = null): Collection
    {
        $results = [];
        $this->reader(
            static function ($fields) use (&$results, $pathway, $callback) {
                if ($fields['pathwayId'] === $pathway && $fields['activityScore'] !== 0.0) {
                    if ($callback !== null) {
                        $tmp = $callback($fields);
                        if ($tmp !== null) {
                            $results[] = $tmp;
                        }
                    } else {
                        $results[] = $fields;
                    }
                }
            }
        );

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
        $mapId = str_ireplace('path:', '', $pathway);
        $minActivity = INF;
        $maxActivity = -INF;
        $coloringData = $this->readPathway(
            $pathway,
            static function (array $data) use (&$maxActivity, &$minActivity) {
                /** @var Node $node */
                $node = Node::whereAccession($data['nodeId'])->first();
                if ($node !== null && $node->type !== 'mirna') {
                    $val = $data['activityScore'];
                    $maxActivity = max($val, $maxActivity);
                    $minActivity = min($val, $minActivity);

                    return [$node->accession, $val];
                }

                return null;
            }
        )->filter();
        $maxActivity = max($maxActivity, abs($minActivity));
        $coloringData = $coloringData->map(
            static function ($row) use ($maxActivity) {
                [$nodeId, $activity] = $row;
                $activity = ($activity / $maxActivity) * 10; //For KEGG normalizes everything in the [-10; 10] range

                return $nodeId . "\t" . number_format($activity, 6);
            }
        );

        return [
            'mapId'    => $mapId,
            'coloring' => $coloringData->implode(PHP_EOL),
        ];
    }

}
