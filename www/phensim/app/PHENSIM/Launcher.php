<?php

namespace App\PHENSIM;

use App\Exceptions\CommandException;
use App\Exceptions\PHENSIM\LauncherException;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class Launcher
{
    /**
     * @var array
     */
    private array $mithrilCommandBase;

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
    private const  REACTOME                  = '-reactome';
    private const  REMOVE_NODES_FILE         = '-remove-nodes-file';
    private const  PATHWAY_MATRIX_OUTPUT     = '-output-pathway-matrix';
    private const  NODES_MATRIX_OUTPUT       = '-output-nodes-matrix';

    public const SUPPORTED_EVIDENCES = ['STRONG', 'WEAK', 'PREDICTION'];
    public const SUPPORTED_FDRS      = ['BH', 'QV', 'LOC'];

    private array $enrichers = [];
    private array $enricherParameters = [];
    private float $epsilon = 0.001;
    private int $simulationIterations = 100;
    private int $bootstrapIterations = 1000;
    private string $miRNAEnrichmentEvidence = 'STRONG';
    private array $removeNodes = [];
    private string $organism = 'hsa';
    private string $fdrMethod = 'BH';
    private bool $reactome = false;
    private ?int $seed = null;
    private string $inputParametersFilePath;
    private ?string $nonExpressedNodesFilePath = null;

    /**
     * The command line to call PHENSIM
     *
     * @var array
     */
    private array $commandLine = [];

    /**
     * The working directory of this job
     *
     * @var string
     */
    private string $workingDirectory;

    /**
     * The output filename generated after the analysis was performed
     *
     * @var string
     */
    private string $outputFilename;

    /**
     * The output filename for the pathway matrix that will be generated after the analysis is performed
     *
     * @var string
     */
    private string $pathwayMatrixOutputFilename;

    /**
     * The output filename for the nodes matrix that will be generated after the analysis is performed
     *
     * @var string
     */
    private string $nodesMatrixOutputFilename;

    /**
     * A list of temporary files to delete before destruction of this object
     *
     * @var array
     */
    private array $tempFiles = [];

    /**
     * Launcher constructor.
     *
     * @param null|string $directory
     */
    public function __construct(?string $directory = null)
    {
        if ($directory !== null) {
            $this->setWorkingDirectory($directory);
        }
        $this->mithrilCommandBase = [
            config('phensim.java'),
            '-jar',
            config('phensim.mithril'),
            'phensim',
            '-threads',
            config('phensim.threads'),
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
     * Add one or more enricher to this analysis
     *
     * @param array|string $enricher
     *
     * @return $this
     */
    public function addEnricher(array|string $enricher): self
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
    public function setEnrichers(array $enrichers = []): self
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
    public function addEnricherParameters(array|string $param, mixed $value = null): self
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
    public function setEnricherParameters(array $enricherParameters = []): self
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
    public function setEpsilon(float $epsilon = 0.001): self
    {
        $this->epsilon = $epsilon;

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
    public function setSimulationIterations(int $simulationIterations = 100): self
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
    public function setMiRNAEnrichmentEvidence(string $miRNAEnrichmentEvidence = 'STRONG'): self
    {
        $miRNAEnrichmentEvidence = strtoupper($miRNAEnrichmentEvidence);
        if (!in_array($miRNAEnrichmentEvidence, self::SUPPORTED_EVIDENCES, true)) {
            throw new LauncherException("Unsupported evidence type.");
        }
        $this->miRNAEnrichmentEvidence = $miRNAEnrichmentEvidence;

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
    public function setOrganism(string $organism = 'hsa'): self
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
    public function setSeed(?int $seed = null): self
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
     * @param string $workingDirectory
     *
     * @return $this
     */
    public function setWorkingDirectory(string $workingDirectory): self
    {
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
     * Is reactome analysis enabled?
     *
     * @return bool
     */
    public function isReactome(): bool
    {
        return $this->reactome;
    }

    /**
     * Enable or disable reactome analysis
     *
     * @param bool $reactome
     *
     * @return \App\PHENSIM\Launcher
     */
    public function setReactome(bool $reactome = true): self
    {
        $this->reactome = $reactome;

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
     * Get the path of the input file for this simulation
     *
     * @return string
     */
    public function getInputParametersFilePath(): string
    {
        return $this->inputParametersFilePath;
    }

    /**
     * Set the path of the input file for this simulation
     *
     * @param string $inputParametersFilePath
     *
     * @return \App\PHENSIM\Launcher
     */
    public function setInputParametersFilePath(string $inputParametersFilePath): self
    {
        $this->inputParametersFilePath = $inputParametersFilePath;

        return $this;
    }

    /**
     * Get the path of the non-expressed nodes file for this simulation.
     * If no non-expressed nodes are provided the path should be NULL.
     *
     * @return string|null
     */
    public function getNonExpressedNodesFilePath(): ?string
    {
        return $this->nonExpressedNodesFilePath;
    }

    /**
     * Set the path of the non-expressed nodes file for this simulation.
     * To provide no non-expressed nodes set the path to NULL.
     *
     * @param string|null $nonExpressedNodesFilePath
     *
     * @return \App\PHENSIM\Launcher
     */
    public function setNonExpressedNodesFilePath(?string $nonExpressedNodesFilePath): self
    {
        $this->nonExpressedNodesFilePath = $nonExpressedNodesFilePath;

        return $this;
    }


//    /**
//     * Build the input file for PHENSIM, the command line argument, and returns its name
//     *
//     * @param array $resultArray
//     *
//     * @return void
//     */
//    private function buildInputFile(array &$resultArray): void
//    {
//        if (empty($this->simulationParameters)) {
//            throw new LauncherException('You must specify at least one simulation parameter');
//        }
//        $inputFile = $this->workingDirectory . Utils::tempFilename('phensim_input', 'tsv');
//        $fp = @fopen($inputFile, 'wb');
//        if (!$fp) {
//            throw new LauncherException('Unable to create PHENSIM input file');
//        }
//        foreach ($this->simulationParameters as $parameter => $type) {
//            @fwrite($fp, $parameter . "\t" . $type . PHP_EOL);
//        }
//        @fclose($fp);
//        $this->buildParameter($inputFile, self::INPUT_FILE, $resultArray);
//        $this->inputFiles[] = $inputFile;
//    }
//
//    /**
//     * Build the file containing non-expressed nodes.
//     *
//     * @param array $resultArray
//     *
//     * @return void
//     */
//    private function buildNonExpressedFile(array &$resultArray): void
//    {
//        if (empty($this->nonExpressedNodes)) {
//            return;
//        }
//        $nonExpFile = $this->workingDirectory . Utils::tempFilename('phensim_nonexp', 'txt');
//        $fp = @fopen($nonExpFile, 'wb');
//        if (!$fp) {
//            throw new LauncherException('Unable to create PHENSIM non-expressed nodes file');
//        }
//        foreach ($this->nonExpressedNodes as $node) {
//            @fwrite($fp, $node . PHP_EOL);
//        }
//        @fclose($fp);
//        $this->buildParameter($nonExpFile, self::NON_EXPRESSED_FILE, $resultArray);
//        $this->inputFiles[] = $nonExpFile;
//    }

    /**
     * Build the file containing nodes to be removed.
     *
     * @return void
     */
    private function buildRemoveNodesFile(): void
    {
        if (empty($this->removeNodes)) {
            return;
        }
        $removeNodesFile = $this->workingDirectory . Utils::tempFilename('phensim_removed', 'txt');
        file_put_contents($removeNodesFile, implode(PHP_EOL, $this->removeNodes));
        $this->appendParameter($removeNodesFile, self::REMOVE_NODES_FILE);
        $this->tempFiles[] = $removeNodesFile;
    }

    /**
     * Build the output file name and command line argument
     *
     * @return void
     */
    private function appendOutputFiles(): void
    {
        $outputFile = $this->workingDirectory . Utils::tempFilename('phensim_output', 'tsv');
        $this->appendParameter($outputFile, self::OUTPUT_FILE);
        $this->outputFilename = $outputFile;
        $outputPathwayMatrix = $this->workingDirectory . Utils::tempFilename('phensim_output_pathway_matrix', 'tsv');
        $this->appendParameter($outputPathwayMatrix, self::PATHWAY_MATRIX_OUTPUT);
        $this->pathwayMatrixOutputFilename = $outputPathwayMatrix;
        $outputNodesMatrix = $this->workingDirectory . Utils::tempFilename('phensim_output_nodes_matrix', 'tsv');
        $this->appendParameter($outputNodesMatrix, self::NODES_MATRIX_OUTPUT);
        $this->nodesMatrixOutputFilename = $outputNodesMatrix;
    }

    /**
     * Append a list parameter to the PHENSIM command
     *
     * @param array  $value
     * @param string $parameter
     *
     * @return void
     */
    private function appendListParameter(array $value, string $parameter): void
    {
        if (!empty($value)) {
            $this->commandLine[] = $parameter;
            $this->commandLine[] = implode(',', $value);
        }
    }

    /**
     * Append a parameter to the PHENSIM command
     *
     * @param mixed  $value
     * @param string $parameter
     *
     * @return void
     */
    private function appendParameter(mixed $value, string $parameter): void
    {
        if (!empty($value)) {
            $this->commandLine[] = $parameter;
            $this->commandLine[] = $value;
        }
    }

    /**
     * Append the -p parameter for the command line
     *
     * @return void
     */
    private function appendEnricherParameters(): void
    {
        if (!empty($this->enricherParameters)) {
            foreach ($this->enricherParameters as $param => $value) {
                $this->commandLine[] = self::ENRICHER_PARAM;
                $this->commandLine[] = sprintf(self::ENRICHER_PARAM_VALUE, $param, $value);
            }
        }
    }

    /**
     * Build the command to run phensim using parameters provided by the user
     *
     * @return void
     */
    private function buildCommandLine(): void
    {
        $this->commandLine = $this->mithrilCommandBase;
        $this->appendParameter($this->inputParametersFilePath, self::INPUT_FILE);
        $this->appendParameter($this->nonExpressedNodesFilePath, self::NON_EXPRESSED_FILE);
        $this->buildRemoveNodesFile();
        $this->appendOutputFiles();
        $this->appendListParameter($this->enrichers, self::ENRICHER);
        $this->appendEnricherParameters();
        $this->appendParameter(sprintf(self::EPSILON_VALUE, $this->epsilon), self::EPSILON);
        $this->appendParameter($this->simulationIterations, self::SIMULATION_ITERATIONS);
        $this->appendParameter($this->bootstrapIterations, self::BOOTSTRAP_ITERATIONS);
        $this->appendParameter($this->miRNAEnrichmentEvidence, self::MIRNA_ENRICHMENT_EVIDENCE);
        $this->appendParameter($this->organism, self::ORGANISM);
        $this->appendParameter($this->seed, self::SEED);
        $this->commandLine[] = self::VERBOSE;
        if ($this->reactome) {
            $this->commandLine[] = self::REACTOME;
        }
    }

    /**
     * Run the FDR computation algorithm
     *
     * @param callable|null $callback
     *
     * @return void
     */
    private function runFDR(?callable $callback = null): void
    {
        if ($this->fdrMethod !== 'BH') {
            $fdrCommand = [
                config('phensim.rscript'),
                config('phensim.fdr'),
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
        $this->buildCommandLine();
        $commandOutput = null;
        $result = null;
        try {
            Utils::runCommand($this->commandLine, $this->getWorkingDirectory(), null, $callback);
            $this->runFDR($callback);
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
        $this->buildCommandLine();

        return $this->commandLine;
    }


    /**
     * Delete all temporary input files for PHENSIM
     *
     * @return bool
     */
    public function deleteInputFiles(): bool
    {
        if (!empty($this->tempFiles)) {
            $success = true;
            foreach ($this->tempFiles as $file) {
                $success = $success && @unlink($file);
            }
            $this->tempFiles = [];

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
