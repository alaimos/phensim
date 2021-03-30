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
use PhpParser\Node\Expr\Cast\Double;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class Reader
{

    public const FIELDS_ALL = [
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

    /*
     * I know this is not a good way but the deadline is coming!!
     */
    public const FIELD_OLD = [
        13 => [
            'pathwayId',
            'pathwayName',
            'nodeId',
            'nodeName',
            'isEndpoint',
            'isDirectTarget',
            'activityScore',
            'pValue',
            'LL',
            'pathwayActivityScore',
            'pathwayPValue',
            'pathwayLL',
            'targetedBy',
        ],
        14 => [
            'pathwayId',
            'pathwayName',
            'nodeId',
            'nodeName',
            'isEndpoint',
            'isDirectTarget',
            'activityScore',
            'pValue',
            'LL',
            'pathwayActivityScore',
            'pathwayPValue',
            'pathwayLL',
            'targetedBy',
            'probabilities',
        ],
        15 => [
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
        ],
    ];

    public const FIELDS_CAST = [
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
        'probabilities'              => 'ignore',
    ];
    public const LL          = ['activation', 'inhibition', 'other'];

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
     * Remove the reader cache for a phensim simulation
     *
     * @param  string  $phensimFile
     *
     * @return void
     */
    public static function cleanupCache(string $phensimFile): void
    {
        $cacheDirectory = dirname($phensimFile) . DIRECTORY_SEPARATOR . 'reader_cache';
        if (is_dir($cacheDirectory) && file_exists($cacheDirectory)) {
            Utils::delete($cacheDirectory);
        }
    }

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
                    $n = count($fields);
                    if ($n === $max || isset(self::FIELD_OLD[$n])) {
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
     * @param  array  $lineData
     *
     * @return array
     */
    private function prepare(array $lineData): array
    {
        $n = count($lineData);
        $lineData = array_combine(self::FIELD_OLD[$n] ?? self::FIELDS_ALL, $lineData);
        foreach ($lineData as $field => $value) {
            $lineData[$field] = $this->cast($field, $value);
        }

        if (isset(self::FIELD_OLD[$n])) {
            foreach (self::FIELDS_ALL as $field) {
                if (!isset($lineData[$field])) {
                    $lineData[$field] = $this->cast($field, '');
                }
            }
        }

        return $lineData;
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
     * Checks if this simulation has results for a specific pathway
     *
     * @param  string  $pathway
     *
     * @return bool
     */
    public function hasPathway(string $pathway): bool
    {
        return file_exists($this->workingDirectory . DIRECTORY_SEPARATOR . 'pathway_' . Str::slug($pathway) . '.json');
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
     * @param  string  $organism
     *
     * @return string
     * @throws \Throwable
     */
    public function makePathwayImage(string $pathway, string $organism): string
    {
        $outputFile = $this->workingDirectory . DIRECTORY_SEPARATOR . 'pathway_' . Str::slug($pathway) . '.png';
        if (!file_exists($outputFile)) {
            $inputFile = $this->workingDirectory . DIRECTORY_SEPARATOR . 'pathway_' . Str::slug($pathway) . '.json';
            if (!file_exists($inputFile)) {
                throw new ReaderException(sprintf('Pathway "%s" not found', $pathway));
            }
            try {
                Utils::runCommand(
                    [
                        config('phensim.rscript'),
                        config('phensim.build_graph'),
                        '-i',
                        $inputFile,
                        '-p',
                        $pathway,
                        '-g',
                        $organism,
                        '-o',
                        $outputFile,
                    ],
                    $this->workingDirectory,
                    60
                );
            } catch (ProcessFailedException $e) {
                throw Utils::mapCommandException(
                    $e,
                    [
                        101 => Utils::IGNORED_ERROR_CODE,
                        102 => 'Unable to build intermediate output file.',
                        103 => 'Unable to build output file',
                    ]
                );
            }
        }

        return $outputFile;
    }

}
