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
        'll',
        'pathwayActivityScore',
        'pathwayPValue',
        'pathwayll',
        'targetedBy',
        'probabilities',
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
        'll'                   => 'll',
        'pathwayActivityScore' => 'double',
        'pathwayPValue'        => 'double',
        'pathwayll'            => 'll',
        'targetedBy'           => 'array',
        'probabilities'        => 'll_prob',
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
        if (self::FIELDS_CAST[$field] === 'll_prob') {
            $tmp = array_map('doubleval', explode(",", $value));
            if (count($tmp) === 4) {
                $act = $tmp[3];
            } else {
                $act = null;
            }
            $tmp = array_slice($tmp, 0, 3);
            if (count($tmp) < 3) {
                $tmp = [null, null, null];
            }
            $ll = array_combine(self::LL, $tmp);
            if ($act === null && $ll['activation'] !== null && $ll['inhibition'] !== null) {
                if ($ll['activation'] > $ll['inhibition']) {
                    $act = abs($ll['activation'] - $ll['inhibition']);
                } elseif ($ll['activation'] < $ll['inhibition']) {
                    $act = -abs($ll['inhibition'] - $ll['activation']);
                } else {
                    $act = 0.0;
                }
            }
            $ll['activity'] = $act;

            return $ll;
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
        if ($n === 12) {
            $fields[] = '';
            $n++;
        }
        if ($n === 13) {
            $fields[] = '';
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
     * Returns the filename of PHENSIM output file
     *
     * @return string
     */
    public function getOutputFilename(): string
    {
        return (string)$this->outputFile;
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
                        'll'            => $fields['pathwayll'],
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
                    if ($fields['probabilities'] !== null && $fields['probabilities']['activity'] !== null) {
                        $fields['activityScore2'] = $fields['probabilities']['activity'];
                        unset($fields['probabilities']['activity']);
                    }
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
     * @param bool   $new
     *
     * @return array
     */
    public function makePathwayColoring(string $pathway, bool $new = false): array
    {
        $mapId = str_ireplace('path:', '', $pathway);
        $coloringData = $this->readPathway(
            $pathway,
            static function (array $data) use ($new) {
                /** @var Node $node */
                $node = Node::whereAccession($data['nodeId'])->first();
                if ($node !== null && $node->type !== 'mirna') {
                    if ($new) {
                        return $node->accession."\t".$data['activityScore2'];
                        /*if ($data['activityScore2'] > 0) {
                            return sprintf(self::ACTIVATION_COLORING, $node->accession);
                        }

                        if ($data['activityScore2'] < 0) {
                            return sprintf(self::INHIBITION_COLORING, $node->accession);
                        }*/
                    } else {
                        return $node->accession."\t".$data['activityScore'];
                        /*if ($data['activityScore'] > 0) {
                            return sprintf(self::ACTIVATION_COLORING, $node->accession);
                        }

                        if ($data['activityScore'] < 0) {
                            return sprintf(self::INHIBITION_COLORING, $node->accession);
                        }*/
                    }
                }

                return null;
            }
        );

        return [
            'mapId'    => $mapId,
            'coloring' => $coloringData->implode(PHP_EOL),
        ];
    }

}
