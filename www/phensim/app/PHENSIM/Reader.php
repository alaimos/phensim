<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

namespace App\PHENSIM;

use App\Exceptions\PHENSIM\ReaderException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        'averagePerturbation',
        'averagePathwayPerturbation',
    ];
    public const FIELDS_CAST         = [
        'pathwayId'                  => null,
        'pathwayName'                => 'pathway',
        'nodeId'                     => null,
        'nodeName'                   => null,
        'isEndpoint'                 => 'boolean',
        'isDirectTarget'             => 'boolean',
        'activityScore'              => 'double',
        'pValue'                     => 'double',
        'FDR'                        => 'double',
        'LL'                         => 'll',
        'pathwayActivityScore'       => 'double',
        'pathwayPValue'              => 'double',
        'pathwayFDR'                 => 'double',
        'pathwayLL'                  => 'll',
        'targetedBy'                 => 'array',
        'averagePerturbation'        => 'double',
        'averagePathwayPerturbation' => 'double',
    ];
    public const LL                  = ['activation', 'inhibition', 'other'];
    public const ACTIVATION_COLORING = '%s red,black';
    public const INHIBITION_COLORING = '%s blue,yellow';
    public const GID_RXP             = '/^[0-9]+$/';

    /**
     * The phensim input file
     *
     * @var string
     */
    private string $phensimFile;

    /**
     * The working directory
     *
     * @var string
     */
    private string $workingDirectory;

    /**
     * Reader Constructor
     *
     * @param  string  $phensimFile
     *
     * @throws \App\Exceptions\FileSystemException|\JsonException
     */
    public function __construct(string $phensimFile)
    {
        if (!file_exists($phensimFile)) {
            throw new ReaderException('Phensim file does not exist');
        }
        $this->phensimFile = $phensimFile;
        $this->workingDirectory = dirname($phensimFile) . DIRECTORY_SEPARATOR . 'reader_cache';
        Utils::createDirectory($this->workingDirectory);
        $this->initialize();
    }

    /**
     * Initialize the cache by reading all pathways and nodes inside PHENSIM output file
     * and write them to json files
     *
     * @throws \JsonException
     */
    private function initialize(): void
    {
        if (!file_exists($this->workingDirectory . DIRECTORY_SEPARATOR . 'pathways.json')) {
            $pathways = [];
            $nodesByPathway = [];
            $fp = @fopen($this->phensimFile, 'rb');
            if (!$fp) {
                throw new ReaderException('Unable to open phensim output file');
            }
            $max = count(self::FIELDS_ALL);
            while (($line = fgets($fp)) !== false) {
                $line = trim($line);
                if (!empty($line) && !str_starts_with($line, '#')) {
                    $fields = str_getcsv($line, "\t");
                    if (count($fields) === $max) {
                        $fields = $this->prepare($fields);
                        $pId = $fields['pathwayId'];
                        if (!isset($pathways[$pId])) {
                            $pathways[$pId] = [
                                'pathwayId'                  => $pId,
                                'pathwayName'                => $fields['pathwayName'],
                                'pathwayActivityScore'       => $fields['pathwayActivityScore'],
                                'pathwayPValue'              => $fields['pathwayPValue'],
                                'pathwayFDR'                 => $fields['pathwayFDR'],
                                'averagePathwayPerturbation' => $fields['averagePathwayPerturbation'],
                            ];
                            $nodesByPathway[$pId] = [];
                        }
                        $nodesByPathway[$pId][] = [
                            'nodeId'              => $fields['nodeId'],
                            'nodeName'            => $fields['nodeName'],
                            'isEndpoint'          => $fields['isEndpoint'],
                            'activityScore'       => $fields['activityScore'],
                            'pValue'              => $fields['pValue'],
                            'FDR'                 => $fields['FDR'],
                            'averagePerturbation' => $fields['averagePerturbation'],
                        ];
                    }
                }
            }
            @fclose($fp);
            file_put_contents(
                $this->workingDirectory . DIRECTORY_SEPARATOR . 'pathways.json',
                json_encode(['data' => $pathways], JSON_THROW_ON_ERROR)
            );
            foreach ($nodesByPathway as $pId => $data) {
                $filename = $this->workingDirectory . DIRECTORY_SEPARATOR . 'pathway_' . Str::slug($pId) . '.json';
                file_put_contents($filename, json_encode(['data' => $data], JSON_THROW_ON_ERROR));
            }
        }
    }

    /**
     * Modifies a file to use its data for subsequent analysis
     *
     * @param  string  $field
     * @param  string  $value
     *
     * @return float|bool|array|string|null
     */
    private function cast(string $field, string $value): float|null|bool|array|string
    {
        if (self::FIELDS_CAST[$field] === 'boolean') {
            return (strtolower($value) === 'yes');
        }
        if (self::FIELDS_CAST[$field] === 'double') {
            return (float)$value;
        }
//        if (self::FIELDS_CAST[$field] === 'll') {
//            $tmp = array_map('doubleval', explode(",", $value));
//            $tmp = array_slice($tmp, 0, 3);
//            if (count($tmp) < 3) {
//                $tmp = [null, null, null];
//            }
//
//            return array_combine(self::LL, $tmp);
//        }
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
     * @param  array  $fields
     *
     * @return array
     */
    private function prepare(array $fields): array
    {
        $fields = array_combine(self::FIELDS_ALL, $fields);
        foreach ($fields as $key => $value) {
            $fields[$key] = $this->cast($key, $value);
        }

        return $fields;
    }

    /**
     * Read list of pathways contained in the simulation
     *
     * @return \Illuminate\Support\Collection
     * @throws \JsonException
     */
    public function readPathwaysList(): Collection
    {
        $file = $this->workingDirectory . DIRECTORY_SEPARATOR . 'pathways.json';
        if (!file_exists($file)) {
            throw new ReaderException('Unable to find pathway list');
        }
        $data = json_decode(
            file_get_contents($file),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return collect($data['data']);
    }

    /**
     * Read the list of altered genes for a single pathway
     *
     * @param  string  $pathway
     *
     * @return \Illuminate\Support\Collection
     * @throws \JsonException
     */
    public function readPathway(string $pathway): Collection
    {
        $file = $this->workingDirectory . DIRECTORY_SEPARATOR . 'pathway_' . Str::slug($pathway) . '.json';
        if (!file_exists($file)) {
            throw new ReaderException(sprintf('Pathway "%s" not found', $pathway));
        }
        $data = json_decode(
            file_get_contents($file),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return collect($data['data']);
    }

    /**
     * Builds an image to show the pathway graph
     *
     * @param  string  $pathway
     *
     * @return string
     */
    public function makePathwayImage(string $pathway): string
    {
        $file = $this->workingDirectory . DIRECTORY_SEPARATOR . 'pathway_' . Str::slug($pathway) . '.png';
        if (!file_exists($file)) {
            $file = $this->workingDirectory . DIRECTORY_SEPARATOR . 'pathway_' . Str::slug($pathway) . '.json';
            if (!file_exists($file)) {
                throw new ReaderException(sprintf('Pathway "%s" not found', $pathway));
            }
            //todo
        }

        return $file;
    }

}
