<?php
/**
 * PHENSIM: Phenotype Simulator
 * @version 2.0.0.2
 * @author  Salvatore Alaimo, Ph.D.
 */

namespace App\PHENSIM;

use App\Exceptions\CommandException;
use App\Exceptions\PHENSIM\LauncherException;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class Launcher
{

    //region Constants

    /*
     * Set of constants used to build PHENSIM command parameters.
     */
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
    private const  FAST                      = '-fast';
    private const  REMOVE_NODES_FILE         = '-remove-nodes-file';
    private const  PATHWAY_MATRIX_OUTPUT     = '-output-pathway-matrix';
    private const  NODES_MATRIX_OUTPUT       = '-output-nodes-matrix';
    public const   EVIDENCE_STRONG           = 'STRONG';
    public const   EVIDENCE_WEAK             = 'WEAK';
    public const   EVIDENCE_PREDICTION       = 'PREDICTION';
    public const   SUPPORTED_EVIDENCES       = [self::EVIDENCE_STRONG, self::EVIDENCE_WEAK, self::EVIDENCE_PREDICTION];
    public const   OVEREXPRESSION            = 'OVEREXPRESSION';
    public const   UNDEREXPRESSION           = 'UNDEREXPRESSION';
    public const   FDR_BH                    = 'BH';   // Benjamini-Hochberg method
    public const   FDR_QV                    = 'QV';   // q-value method
    public const   FDR_LOC                   = 'LOC';  // Local-FDR method
    public const   FDRS_NAMES                = [
        self::FDR_BH  => 'Benjamini & Hochberg',
        self::FDR_QV  => 'Q-value (Storey et al.)',
        self::FDR_LOC => 'Local FDR (Efron et al.)',
    ];
    public const   SUPPORTED_FDRS            = [self::FDR_BH, self::FDR_QV, self::FDR_LOC];
    public const   MIRNA_ENRICHER            = 'mirna';

    //endregion

    //region Private variables

    /**
     * The base command used to call PHENSIM executable inside the MITHrIL package
     *
     * @var array
     */
    private array $mithrilCommandBase;

    /**
     * A list of enrichers algorithm used to modify pathway topology
     * @var array
     */
    private array $enrichers = [];

    /**
     * A map of parameters passed to the algorithms
     * @var array
     */
    private array $enricherParameters = [];

    /**
     * Epsilon value used to predict non-expression of a node
     *
     * @var float
     */
    private float $epsilon = 0.001;

    /**
     * Number of iterations for the p-value simulation
     *
     * @var int
     */
    private int $simulationIterations = 100;

    /**
     * Number of iterations for the bootstrapping procedure
     *
     * @var int
     */
    private int $bootstrapIterations = 1000;

    /**
     * Evidence level for miRNA enrichment
     *
     * @var string
     */
    private string $miRNAEnrichmentEvidence = 'STRONG';

    /**
     * Set of knocked-out nodes
     *
     * @var array
     */
    private array $removeNodes = [];

    /**
     * Organism of the simulation
     *
     * @var string
     */
    private string $organism = 'hsa';

    /**
     * FDR computation method
     *
     * @var string
     */
    private string $fdrMethod = 'BH';

    /**
     * Should REACTOME be used with KEGG pathways?
     *
     * @var bool
     */
    private bool $reactome = false;

    /**
     * Should FAST method be used?
     *
     * @var bool
     */
    private bool $fast = true;

    /**
     * Seed for the RNG. Set to null for random seed generation.
     *
     * @var int|null
     */
    private ?int $seed = null;

    /**
     * Path of PHENSIM input file
     *
     * @var string
     */
    private string $inputParametersFilePath;

    /**
     * Path of the non-expressed nodes file
     *
     * @var string|null
     */
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

    //endregion

    /**
     * Launcher constructor.
     *
     * @param  null|string  $directory
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

    //region Setters

    /**
     * Set the path of the input file for this simulation
     *
     * @param  string  $inputParametersFilePath
     *
     * @return $this
     */
    public function setInputParametersFilePath(string $inputParametersFilePath): self
    {
        $this->inputParametersFilePath = $inputParametersFilePath;

        return $this;
    }

    /**
     * Set the path of the non-expressed nodes file for this simulation.
     * To provide no non-expressed nodes set the path to NULL.
     *
     * @param  string|null  $nonExpressedNodesFilePath
     *
     * @return $this
     */
    public function setNonExpressedNodesFilePath(?string $nonExpressedNodesFilePath): self
    {
        $this->nonExpressedNodesFilePath = $nonExpressedNodesFilePath;

        return $this;
    }

    /**
     * Add one or more enricher to this analysis
     *
     * @param  array|string  $enricher
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
     * @param  array  $enrichers
     *
     * @return $this
     */
    public function setEnrichers(array $enrichers = []): self
    {
        $this->enrichers = $enrichers;

        return $this;
    }

    /**
     * Add all parameters needed to setup a local db file enrichment in PHENSIM
     *
     * @param  string  $inputFile
     * @param  string|null  $filter
     * @param  string|null  $nodeTypesFile
     * @param  string|null  $edgeTypesFile
     * @param  string|null  $edgeSubTypesFile
     *
     * @return $this
     */
    public function setDBEnricher(
        string $inputFile,
        ?string $filter = null,
        ?string $nodeTypesFile = null,
        ?string $edgeTypesFile = null,
        ?string $edgeSubTypesFile = null
    ): self {
        if (!file_exists($inputFile)) {
            throw new LauncherException('An invalid input file for the DB enrichment has been provided');
        }
        $this->addEnricher('textEnricher')->addEnricherParameters('inputFile', $inputFile);
        if (!empty($filter)) {
            $this->addEnricherParameters('filter', $filter);
        }
        if (!empty($nodeTypesFile) && file_exists($nodeTypesFile)) {
            $this->addEnricherParameters('nodeTypesFile', $nodeTypesFile);
        }
        if (!empty($edgeTypesFile) && file_exists($edgeTypesFile)) {
            $this->addEnricherParameters('edgeTypesFile', $edgeTypesFile);
        }
        if (!empty($edgeSubTypesFile) && file_exists($edgeSubTypesFile)) {
            $this->addEnricherParameters('edgeSubTypesFile', $edgeSubTypesFile);
        }

        return $this;
    }

    /**
     * Set one or more parameters for the enrichers
     *
     * @param  array|string  $param
     * @param  null|mixed  $value
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
     * @param  array  $enricherParameters
     *
     * @return $this
     */
    public function setEnricherParameters(array $enricherParameters = []): self
    {
        $this->enricherParameters = $enricherParameters;

        return $this;
    }

    /**
     * Set the value of the epsilon parameter for PHENSIM
     *
     * @param  float  $epsilon
     *
     * @return $this
     */
    public function setEpsilon(float $epsilon = 0.001): self
    {
        $this->epsilon = $epsilon;

        return $this;
    }

    /**
     * Set the number of iterations used for the simulation cycle
     *
     * @param  int  $simulationIterations
     *
     * @return $this
     */
    public function setSimulationIterations(int $simulationIterations = 100): self
    {
        $this->simulationIterations = $simulationIterations;

        return $this;
    }

    /**
     * Set the number of iterations used for the bootstrapping procedure
     *
     * @param  int  $bootstrapIterations
     *
     * @return $this
     */
    public function setBootstrapIterations(int $bootstrapIterations = 1000): self
    {
        $this->bootstrapIterations = $bootstrapIterations;

        return $this;
    }

    /**
     * Set the type of evidence used for the enrichment with microRNAs (if enabled).
     * Allowed types are: "STRONG", "WEAK", "PREDICTION"
     *
     * @param  string  $miRNAEnrichmentEvidence
     *
     * @return $this
     */
    public function setMiRNAEnrichmentEvidence(string $miRNAEnrichmentEvidence = self::EVIDENCE_STRONG): self
    {
        $miRNAEnrichmentEvidence = strtoupper($miRNAEnrichmentEvidence);
        if (!in_array($miRNAEnrichmentEvidence, self::SUPPORTED_EVIDENCES, true)) {
            throw new LauncherException("Unsupported evidence type.");
        }
        $this->miRNAEnrichmentEvidence = $miRNAEnrichmentEvidence;

        return $this;
    }

    /**
     * Set the list of nodes that will be removed to simulate a knockout
     *
     * @param  array  $removeNodes
     *
     * @return $this
     */
    public function setRemoveNodes(array $removeNodes = []): self
    {
        $this->removeNodes = $removeNodes;

        return $this;
    }

    /**
     * Set the organism used for the current analysis
     *
     * @param  string  $organism
     *
     * @return $this
     */
    public function setOrganism(string $organism): self
    {
        $this->organism = $organism;

        return $this;
    }

    /**
     * Set the seed of the random number generator
     *
     * @param  null|int  $seed
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
     * @param  string  $workingDirectory
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
     * Set the method used for FDR computation
     *
     * @param  string  $fdrMethod
     *
     * @return $this
     */
    public function setFdrMethod(string $fdrMethod = self::FDR_BH): self
    {
        if (!in_array($fdrMethod, self::SUPPORTED_FDRS)) {
            $fdrMethod = 'BH';
        }
        $this->fdrMethod = $fdrMethod;

        return $this;
    }

    /**
     * Enable or disable reactome analysis
     *
     * @param  bool  $reactome
     *
     * @return $this
     */
    public function setReactome(bool $reactome = true): self
    {
        $this->reactome = $reactome;

        return $this;
    }


    /**
     * Enable or disable fast analysis
     *
     * @param  bool  $fast
     *
     * @return $this
     */
    public function setFast(bool $fast = true): self
    {
        $this->fast = $fast;

        return $this;
    }

    //endregion

    /**
     * Build the input file for PHENSIM, the command line argument, and returns its name
     *
     * @param  array  $overExpressedAccessions
     * @param  array  $underExpressedAccessions
     *
     * @return $this
     */
    public function buildInputFile(array $overExpressedAccessions = [], array $underExpressedAccessions = []): self
    {
        if (empty($overExpressedAccessions) && empty($underExpressedAccessions)) {
            throw new LauncherException('You must specify at least one simulation parameter');
        }
        $inputFile = $this->workingDirectory . Utils::tempFilename('phensim_input_', '.tsv');
        $overExpressedAccessions = array_map(static fn($n) => $n . "\t" . self::OVEREXPRESSION, $overExpressedAccessions);
        $underExpressedAccessions = array_map(static fn($n) => $n . "\t" . self::UNDEREXPRESSION, $underExpressedAccessions);
        if (file_put_contents($inputFile, implode(PHP_EOL, array_merge($overExpressedAccessions, $underExpressedAccessions))) === false) {
            throw new LauncherException('Unable to create PHENSIM input file');
        }
        $this->setInputParametersFilePath($inputFile);

        return $this;
    }

    /**
     * Build and set the file containing non-expressed nodes from an array of accession numbers
     *
     * @param  array  $accessions
     *
     * @return $this
     */
    public function buildNonExpressedFile(array $accessions): self
    {
        if (empty($accessions)) {
            $nonExpFile = null;
        } else {
            $nonExpFile = $this->workingDirectory . Utils::tempFilename('phensim_non_exp_', '.txt');
            if (file_put_contents($nonExpFile, implode(PHP_EOL, $accessions)) === false) {
                throw new LauncherException('Unable to create PHENSIM non-expressed nodes file');
            }
        }

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

    //region Command Line Builder

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
        $removeNodesFile = $this->workingDirectory . Utils::tempFilename('phensim_removed_', '.txt');
        if (file_put_contents($removeNodesFile, implode(PHP_EOL, $this->removeNodes)) === false) {
            throw new LauncherException('Unable to create PHENSIM knockout nodes file');
        }
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
     * @param  array  $value
     * @param  string  $parameter
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
     * @param  mixed  $value
     * @param  string  $parameter
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
        if ($this->fast) {
            $this->commandLine[] = self::FAST;
        }
    }

    //endregion

    /**
     * Run the FDR computation algorithm
     *
     * @param  callable|null  $callback
     *
     * @return void
     */
    private function runFDR(?callable $callback = null): void
    {
        if ($this->fdrMethod !== self::FDR_BH) {
            $fdrCommand = [
                config('phensim.rscript'),
                config('phensim.fdr'),
                '-i',
                $this->outputFilename,
                '-o',
                $this->outputFilename,
            ];
            if ($this->fdrMethod === self::FDR_LOC) {
                $fdrCommand[] = '-l';
            }
            Utils::runCommand($fdrCommand, $this->workingDirectory, null, $callback);
        }
    }

    /**
     * Run PHENSIM.
     *
     * @param  callable|null  $callback
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
            Utils::runCommand($this->commandLine, $this->workingDirectory, null, $callback);
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
     * @return void
     */
    public function deleteTempFiles(): void
    {
        if (!empty($this->tempFiles)) {
            foreach ($this->tempFiles as $file) {
                @unlink($file);
            }
            $this->tempFiles = [];
        }
    }

    /**
     * Clears all temporary files created by this object
     *
     * @return void
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    public function __destruct()
    {
        $this->deleteTempFiles();
    }


}
