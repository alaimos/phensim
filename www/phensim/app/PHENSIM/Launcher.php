<?php

namespace App\PHENSIM;

use App\Exceptions\CommandException;
use App\Models\Job;
use App\PHENSIM\Exception\LauncherException;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class Launcher
{
    /**
     * @var array
     */
    private $mithrilCommandBase;

    private const  ENRICHER                  = '-e';
    private const  ENRICHER_PARAM            = '-p';
    private const  ENRICHER_PARAM_VALUE      = '%s=%s';
    private const  EPSILON                   = '-epsilon';
    private const  EPSILON_VALUE             = '%.10f';
    private const  INPUT_FILE                = '-i';
    private const  SIMULATION_ITERATIONS     = '-number-of-iterations-simulation';
    private const  BOOTSTRAP_ITERATIONS      = '-number-of-iterations';
    private const  MIRNA_ENRICHMENT_EVIDENCE = '-enrichment-evidence-type';
    private const  NON_EXPRESSED_FILE        = '-non-expressed-file';
    private const  ORGANISM                  = '-organism';
    private const  OUTPUT_FILE               = '-o';
    private const  SEED                      = '-seed';
    private const  VERBOSE                   = '-verbose';
    private const  REMOVE_NODES_FILE         = '-remove-nodes-file';
    private const  PATHWAY_MATRIX_OUTPUT     = '-output-pathway-matrix';
    private const  NODES_MATRIX_OUTPUT       = '-output-nodes-matrix';

    public const SUPPORTED_EVIDENCES = ['STRONG', 'WEAK', 'PREDICTION'];
    public const OVEREXPRESSION      = 'OVEREXPRESSION';
    public const UNDEREXPRESSION     = 'UNDEREXPRESSION';
    public const BOTH                = 'BOTH';
    public const SUPPORTED_FDRS      = ['BH', 'QV', 'LOC'];

    private $enrichers = [];
    private $enricherParameters = [];
    private $epsilon = 0.001;
    private $simulationParameters = [];
    private $simulationIterations = 100;
    private $bootstrapIterations = 1000;
    private $miRNAEnrichmentEvidence = 'STRONG';
    private $nonExpressedNodes = [];
    private $removeNodes = [];
    private $organism = 'hsa';
    private $fdrMethod = 'BH';
    private $seed = null;

    /**
     * The working directory of this job
     *
     * @var string
     */
    private $workingDirectory;

    /**
     * The output filename generated after the analysis was performed
     *
     * @var string
     */
    private $outputFilename;

    /**
     * The output filename for the pathway matrix that will be generated after the analysis is performed
     *
     * @var string
     */
    private $pathwayMatrixOutputFilename;

    /**
     * The output filename for the nodes matrix that will be generated after the analysis is performed
     *
     * @var string
     */
    private $nodesMatrixOutputFilename;

    /**
     * A list of input files to delete before destruction of this object
     *
     * @var array
     */
    private $inputFiles = [];

    /**
     * Launcher constructor.
     *
     * @param null|string|\App\Models\Job $directory
     */
    public function __construct($directory = null)
    {
        if ($directory !== null) {
            $this->setWorkingDirectory($directory);
        }
        $this->mithrilCommandBase = [
            env('JAVA_PATH') . '/java',
            '-jar',
            resource_path('bin/MITHrIL2.jar'),
            'phensim',
            '-threads',
            env('PHENSIM_THREADS', 2),
        ];
    }

    /**
     * Get the list of enrichers for this analysis
     *
     * @return array
     */
    public function getEnrichers(): array
    {
        return $this->enrichers;
    }

    /**
     * Add one or more enricher for this analysis
     *
     * @param array|string $enricher
     *
     * @return $this
     */
    public function addEnricher($enricher): self
    {
        if (is_array($enricher)) {
            foreach ($enricher as $e) {
                $this->addEnricher($e);
            }
        } elseif (!in_array($enricher, $this->enrichers, true)) {
            $this->enrichers[] = $enricher;
        }

        return $this;
    }

    /**
     * Set the list of enrichers for this analysis
     *
     * @param array $enrichers
     *
     * @return $this
     */
    public function setEnrichers($enrichers = []): self
    {
        $this->enrichers = $enrichers;

        return $this;
    }

    /**
     * Get all parameters for the enrichers
     *
     * @return array
     */
    public function getEnricherParameters(): array
    {
        return $this->enricherParameters;
    }

    /**
     * Set one or more parameters for the enrichers
     *
     * @param array|string $param
     * @param null|mixed   $value
     *
     * @return $this
     */
    public function addEnricherParameters($param, $value = null): self
    {
        if (is_array($param)) {
            foreach ($this->enricherParameters as $key => $val) {
                $this->enricherParameters[$key] = $val;
            }
        } else {
            $this->enricherParameters[$param] = $value;
        }

        return $this;
    }

    /**
     * Set parameters for the enrichers
     *
     * @param array $enricherParameters
     *
     * @return $this
     */
    public function setEnricherParameters($enricherParameters = []): self
    {
        $this->enricherParameters = $enricherParameters;

        return $this;
    }

    /**
     * Get the current value of the epsilon parameter
     *
     * @return float
     */
    public function getEpsilon(): float
    {
        return $this->epsilon;
    }

    /**
     * Set the value of the epsilon parameter for PHENSIM
     *
     * @param float $epsilon
     *
     * @return $this
     */
    public function setEpsilon($epsilon = 0.001): self
    {
        $this->epsilon = $epsilon;

        return $this;
    }

    /**
     * Get the list of parameters for the simulation
     *
     * @return array
     */
    public function getSimulationParameters(): array
    {
        return $this->simulationParameters;
    }

    /**
     * Add one or more simulation parameters
     *
     * @param string|array $parameter
     * @param string       $expressionChange
     *
     * @return $this
     */
    public function addSimulationParameter($parameter, $expressionChange = self::BOTH): self
    {
        if (is_array($parameter)) {
            foreach ($parameter as $key => $change) {
                $this->addSimulationParameter($key, $change);
            }
        } else {
            $expressionChange = strtoupper($expressionChange);
            if (!in_array($expressionChange, [self::OVEREXPRESSION, self::UNDEREXPRESSION, self::BOTH])) {
                throw new LauncherException('Unsupported expression change');
            }
            $this->simulationParameters[$parameter] = $expressionChange;
        }

        return $this;
    }

    /**
     * Set parameters for the simulation
     *
     * @param array $simulationParameters
     *
     * @return $this
     */
    public function setSimulationParameters($simulationParameters): self
    {
        $this->simulationParameters = [];
        $this->addSimulationParameter($simulationParameters);

        return $this;
    }

    /**
     * Get the number of iterations used for the simulation cycle
     *
     * @return int
     */
    public function getSimulationIterations(): int
    {
        return $this->simulationIterations;
    }

    /**
     * Set the number of iterations used for the simulation cycle
     *
     * @param int $simulationIterations
     *
     * @return $this
     */
    public function setSimulationIterations($simulationIterations = 100): self
    {
        $this->simulationIterations = $simulationIterations;

        return $this;
    }

    /**
     * Get the number of iterations used for the bootstrapping procedure
     *
     * @return int
     */
    public function getBootstrapIterations(): int
    {
        return $this->bootstrapIterations;
    }

    /**
     * Set the number of iterations used for the bootstrapping procedure
     *
     * @param int $bootstrapIterations
     *
     * @return $this
     */
    public function setBootstrapIterations(int $bootstrapIterations = 1000): self
    {
        $this->bootstrapIterations = $bootstrapIterations;

        return $this;
    }


    /**
     * Returns the type of evidence used for the enrichment with microRNAs (if enabled)
     *
     * @return string
     */
    public function getMiRNAEnrichmentEvidence(): string
    {
        return $this->miRNAEnrichmentEvidence;
    }

    /**
     * Set the type of evidence used for the enrichment with microRNAs (if enabled).
     * Allowed types are: "STRONG", "WEAK", "PREDICTION"
     *
     * @param string $miRNAEnrichmentEvidence
     *
     * @return $this
     */
    public function setMiRNAEnrichmentEvidence($miRNAEnrichmentEvidence = 'STRONG'): self
    {
        $miRNAEnrichmentEvidence = strtoupper($miRNAEnrichmentEvidence);
        if (!in_array($miRNAEnrichmentEvidence, self::SUPPORTED_EVIDENCES)) {
            throw new LauncherException("Unsupported evidence type.");
        }
        $this->miRNAEnrichmentEvidence = $miRNAEnrichmentEvidence;

        return $this;
    }

    /**
     * Get a list of non-expressed nodes in the simulation
     *
     * @return array
     */
    public function getNonExpressedNodes(): array
    {
        return $this->nonExpressedNodes;
    }

    /**
     * Set a list of non-expressed nodes in the simulation
     *
     * @param array $nonExpressedNodes
     *
     * @return $this
     */
    public function setNonExpressedNodes($nonExpressedNodes = []): self
    {
        $this->nonExpressedNodes = $nonExpressedNodes;

        return $this;
    }

    /**
     * Get the list of nodes that will be removed to simulate a gene knockout
     *
     * @return array
     */
    public function getRemoveNodes(): array
    {
        return $this->removeNodes;
    }

    /**
     * Set the list of nodes that will be removed to simulate a knockout
     *
     * @param array $removeNodes
     *
     * @return $this
     */
    public function setRemoveNodes(array $removeNodes = []): self
    {
        $this->removeNodes = $removeNodes;

        return $this;
    }


    /**
     * Get the organism used for the current analysis
     *
     * @return string
     */
    public function getOrganism(): string
    {
        return $this->organism;
    }

    /**
     * Set the organism used for the current analysis
     *
     * @param string $organism
     *
     * @return $this
     */
    public function setOrganism($organism = 'hsa'): self
    {
        $this->organism = $organism;

        return $this;
    }

    /**
     * Get the customized seed used for the random number generator
     *
     * @return null|integer
     */
    public function getSeed(): ?int
    {
        return $this->seed;
    }

    /**
     * Set the seed of the random number generator
     *
     * @param null|integer $seed
     *
     * @return $this
     */
    public function setSeed($seed = null): self
    {
        $this->seed = $seed;

        return $this;
    }

    /**
     * Returns the working directory for this analysis
     *
     * @return string
     */
    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * Set the working directory for this analysis
     *
     * @param mixed $workingDirectory
     *
     * @return $this
     */
    public function setWorkingDirectory($workingDirectory): self
    {
        if ($workingDirectory instanceof Job) {
            $workingDirectory = $workingDirectory->getJobDirectory();
        }
        $workingDirectory = rtrim($workingDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->workingDirectory = $workingDirectory;

        return $this;
    }

    /**
     * Get the method used for FDR computation
     *
     * @return string
     */
    public function getFdrMethod(): string
    {
        return $this->fdrMethod;
    }

    /**
     * Set the method used for FDR computation
     *
     * @param string $fdrMethod
     *
     * @return $this
     */
    public function setFdrMethod(string $fdrMethod = 'BH'): self
    {
        if (!in_array($fdrMethod, self::SUPPORTED_FDRS)) {
            $fdrMethod = 'BH';
        }
        $this->fdrMethod = $fdrMethod;

        return $this;
    }


    /**
     * Get the output filename after the analysis was performed
     *
     * @return string
     */
    public function getOutputFilename(): string
    {
        return $this->outputFilename;
    }

    /**
     * Get the output filename of the pathway matrix
     *
     * @return string
     */
    public function getPathwayMatrixOutputFilename(): string
    {
        return $this->pathwayMatrixOutputFilename;
    }

    /**
     * Get the output filename of the nodes matrix
     *
     * @return string
     */
    public function getNodesMatrixOutputFilename(): string
    {
        return $this->nodesMatrixOutputFilename;
    }


    /**
     * Build the input file for PHENSIM, the command line argument, and returns its name
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function buildInputFile(array &$resultArray): void
    {
        if (empty($this->simulationParameters)) {
            throw new LauncherException('You must specify at least one simulation parameter');
        }
        $inputFile = $this->workingDirectory . Utils::tempFilename('phensim_input', 'tsv');
        $fp = @fopen($inputFile, 'wb');
        if (!$fp) {
            throw new LauncherException('Unable to create PHENSIM input file');
        }
        foreach ($this->simulationParameters as $parameter => $type) {
            @fwrite($fp, $parameter . "\t" . $type . PHP_EOL);
        }
        @fclose($fp);
        $this->buildParameter($inputFile, self::INPUT_FILE, $resultArray);
        $this->inputFiles[] = $inputFile;
    }

    /**
     * Build the file containing non-expressed nodes.
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function buildNonExpressedFile(array &$resultArray): void
    {
        if (empty($this->nonExpressedNodes)) {
            return;
        }
        $nonExpFile = $this->workingDirectory . Utils::tempFilename('phensim_nonexp', 'txt');
        $fp = @fopen($nonExpFile, 'wb');
        if (!$fp) {
            throw new LauncherException('Unable to create PHENSIM non-expressed nodes file');
        }
        foreach ($this->nonExpressedNodes as $node) {
            @fwrite($fp, $node . PHP_EOL);
        }
        @fclose($fp);
        $this->buildParameter($nonExpFile, self::NON_EXPRESSED_FILE, $resultArray);
        $this->inputFiles[] = $nonExpFile;
    }

    /**
     * Build the file containing nodes to be removed.
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function buildRemoveNodesFile(array &$resultArray): void
    {
        if (empty($this->removeNodes)) {
            return;
        }
        $removeNodesFile = $this->workingDirectory . Utils::tempFilename('phensim_removed', 'txt');
        $fp = @fopen($removeNodesFile, 'wb');
        if (!$fp) {
            throw new LauncherException('Unable to create PHENSIM non-expressed nodes file');
        }
        foreach ($this->removeNodes as $node) {
            @fwrite($fp, $node . PHP_EOL);
        }
        @fclose($fp);
        $this->buildParameter($removeNodesFile, self::REMOVE_NODES_FILE, $resultArray);
        $this->inputFiles[] = $removeNodesFile;
    }

    /**
     * Build the output file name and command line argument
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function buildOutputFile(array &$resultArray): void
    {
        $outputFile = $this->workingDirectory . Utils::tempFilename('phensim_output', 'tsv');
        $this->buildParameter($outputFile, self::OUTPUT_FILE, $resultArray);
        $this->outputFilename = $outputFile;
        $outputPathwayMatrix = $this->workingDirectory . Utils::tempFilename('phensim_output_pathway_matrix', 'tsv');
        $this->buildParameter($outputPathwayMatrix, self::PATHWAY_MATRIX_OUTPUT, $resultArray);
        $this->pathwayMatrixOutputFilename = $outputPathwayMatrix;
        $outputNodesMatrix = $this->workingDirectory . Utils::tempFilename('phensim_output_nodes_matrix', 'tsv');
        $this->buildParameter($outputNodesMatrix, self::NODES_MATRIX_OUTPUT, $resultArray);
        $this->nodesMatrixOutputFilename = $outputNodesMatrix;
    }

    /**
     * Build a list parameter in the command line
     *
     * @param array  $value
     * @param string $parameter
     * @param array  $resultArray
     *
     * @return void
     */
    private function buildListParameter(array $value, $parameter, array &$resultArray): void
    {
        if (!empty($value)) {
            $resultArray[] = $parameter;
            $resultArray[] = implode(',', $value);
        }
    }

    /**
     * Build a parameter in the command line
     *
     * @param mixed  $value
     * @param string $parameter
     * @param array  $resultArray
     *
     * @return void
     */
    private function buildParameter($value, $parameter, array &$resultArray): void
    {
        if (!empty($value)) {
            $resultArray[] = $parameter;
            $resultArray[] = $value;
        }
    }

    /**
     * Build the -p parameter for the command line
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function buildEnricherParameters(array &$resultArray): void
    {
        if (!empty($this->enricherParameters)) {
            foreach ($this->enricherParameters as $param => $value) {
                $resultArray[] = self::ENRICHER_PARAM;
                $resultArray[] = sprintf(self::ENRICHER_PARAM_VALUE, $param, $value);
            }
        }
    }

    /**
     * Build the command to run phensim using parameters provided by the user
     *
     * @return array
     */
    private function buildCommandLine(): array
    {
        $parameters = $this->mithrilCommandBase;
        $this->buildInputFile($parameters);
        $this->buildNonExpressedFile($parameters);
        $this->buildRemoveNodesFile($parameters);
        $this->buildOutputFile($parameters);
        $this->buildListParameter($this->enrichers, self::ENRICHER, $parameters);
        $this->buildEnricherParameters($parameters);
        $this->buildParameter(sprintf(self::EPSILON_VALUE, $this->epsilon), self::EPSILON, $parameters);
        $this->buildParameter($this->simulationIterations, self::SIMULATION_ITERATIONS, $parameters);
        $this->buildParameter($this->bootstrapIterations, self::BOOTSTRAP_ITERATIONS, $parameters);
        $this->buildParameter($this->miRNAEnrichmentEvidence, self::MIRNA_ENRICHMENT_EVIDENCE, $parameters);
        $this->buildParameter($this->organism, self::ORGANISM, $parameters);
        $this->buildParameter($this->seed, self::SEED, $parameters);
        $parameters[] = self::VERBOSE;

        return $parameters;
    }

    /**
     * Run PHENSIM.
     *
     * @param callable|null $callback
     *
     * @return void
     * @throws CommandException
     * @throws LauncherException
     */
    public function run(?callable $callback = null): void
    {
        $command = $this->buildCommandLine();
        $commandOutput = null;
        $result = null;
        try {
            Utils::runCommand($command, $this->getWorkingDirectory(), null, $callback);
            if ($this->fdrMethod !== 'BH') {
                $fdrCommand = [
                    'Rscript',
                    resource_path('bin/compute_fdrs.R'),
                    '-i',
                    $this->outputFilename,
                    '-o',
                    $this->outputFilename,
                ];
                if ($this->fdrMethod === 'LOC') {
                    $fdrCommand[] = '-l';
                }
                Utils::runCommand($fdrCommand, $this->getWorkingDirectory(), null, $callback);
            }
        } catch (ProcessFailedException $e) {
            Utils::mapCommandException(
                $e,
                [
                    101 => 'Invalid input file: file does not exist.',
                    102 => 'Invalid species: species not found.',
                    103 => 'Unknown error',
                ]
            );
        }
        if (!file_exists($this->outputFilename)) {
            throw new LauncherException('Unable to create output file');
        }
        if (!file_exists($this->pathwayMatrixOutputFilename)) {
            throw new LauncherException('Unable to create pathway matrix output file');
        }
        if (!file_exists($this->nodesMatrixOutputFilename)) {
            throw new LauncherException('Unable to create nodes matrix output file');
        }
    }

    /**
     * Returns the command line used to run PHENSIM
     *
     * @return array
     */
    public function getCommandLine(): array
    {
        return $this->buildCommandLine();
    }


    /**
     * Delete all temporary input files for PHENSIM
     *
     * @return bool
     */
    public function deleteInputFiles(): bool
    {
        if (!empty($this->inputFiles) && is_array($this->inputFiles)) {
            $success = true;
            foreach ($this->inputFiles as $file) {
                $success = $success && @unlink($file);
            }
            $this->inputFiles = null;

            return $success;
        }

        return false;
    }

    /**
     * Clears all temporary files created by this object
     *
     * @return void
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __destruct()
    {
        $this->deleteInputFiles();
    }


}
