<?php

namespace App\PHENSIM;

use App\Exceptions\CommandException;
use App\Models\Job;
use App\PHENSIM\Exception\LauncherException;

final class Launcher
{
    const MITHRIL_JAR                        = 'bin/MITHrIL2.jar';
    const MITHRIL_EXEC                       = '/opt/jdk/bin/java -jar %1$s %2$s %3$s';
    const SIMPATHY                           = 'phensim -m';
    const SIMPATHY_ENRICHERS                 = '-e %s';
    const SIMPATHY_ENRICHER_PARAMETER        = '-p %s=%s';
    const SIMPATHY_EPSILON                   = '-epsilon %.10f';
    const SIMPATHY_INPUT                     = '-i %s';
    const SIMPATHY_ITERATIONS                = '-number-of-iterations %d';
    const SIMPATHY_MIRNA_ENRICHMENT_EVIDENCE = '-enrichment-evidence-type %s';
    const SIMPATHY_NON_EXPRESSED             = '-non-expressed-file %s';
    const SIMPATHY_ORGANISM                  = '-organism %s';
    const SIMPATHY_OUTPUT                    = '-o %s';
    const SIMPATHY_SEED                      = '-seed %d';
    const SIMPATHY_VERBOSE                   = '-verbose';
    const SIMPATHY_SUPPORTED_EVIDENCES       = ['STRONG', 'WEAK', 'PREDICTION'];
    const OVEREXPRESSION                     = 'OVEREXPRESSION';
    const UNDEREXPRESSION                    = 'UNDEREXPRESSION';
    const BOTH                               = 'BOTH';

    private $enrichers               = [];
    private $enricherParameters      = [];
    private $epsilon                 = 0.001;
    private $simulationParameters    = [];
    private $simulationIterations    = 2001;
    private $miRNAEnrichmentEvidence = 'STRONG';
    private $nonExpressedNodes       = [];
    private $organism                = 'hsa';
    private $seed                    = null;

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
    }

    /**
     * Get the list of enrichers for this analysis
     *
     * @return array
     */
    public function getEnrichers()
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
    public function addEnricher($enricher)
    {
        if (is_array($enricher)) {
            foreach ($enricher as $e) {
                $this->addEnricher($e);
            }
        } else {
            if (!in_array($enricher, $this->enrichers)) {
                $this->enrichers[] = $enricher;
            }
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
    public function setEnrichers($enrichers = [])
    {
        $this->enrichers = $enrichers;
        return $this;
    }

    /**
     * Get all parameters for the enrichers
     *
     * @return array
     */
    public function getEnricherParameters()
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
    public function addEnricherParameters($param, $value = null)
    {
        if (is_array($param)) {
            foreach ($this->enricherParameters as $key => $value) {
                $this->enricherParameters[$key] = $value;
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
    public function setEnricherParameters($enricherParameters = [])
    {
        $this->enricherParameters = $enricherParameters;
        return $this;
    }

    /**
     * Get the current value of the epsilon parameter
     *
     * @return float
     */
    public function getEpsilon()
    {
        return $this->epsilon;
    }

    /**
     * Set the value of the epsilon parameter for SIMPATHY
     *
     * @param float $epsilon
     *
     * @return $this
     */
    public function setEpsilon($epsilon = 0.001)
    {
        $this->epsilon = $epsilon;
        return $this;
    }

    /**
     * Get the list of parameters for the simulation
     *
     * @return array
     */
    public function getSimulationParameters()
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
    public function addSimulationParameter($parameter, $expressionChange = self::BOTH)
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
    public function setSimulationParameters($simulationParameters)
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
    public function getSimulationIterations()
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
    public function setSimulationIterations($simulationIterations = 2001)
    {
        $this->simulationIterations = $simulationIterations;
        return $this;
    }

    /**
     * Returns the type of evidence used for the enrichment with microRNAs (if enabled)
     *
     * @return string
     */
    public function getMiRNAEnrichmentEvidence()
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
    public function setMiRNAEnrichmentEvidence($miRNAEnrichmentEvidence = 'STRONG')
    {
        $miRNAEnrichmentEvidence = strtoupper($miRNAEnrichmentEvidence);
        if (!in_array($miRNAEnrichmentEvidence, self::SIMPATHY_SUPPORTED_EVIDENCES)) {
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
    public function getNonExpressedNodes()
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
    public function setNonExpressedNodes($nonExpressedNodes = [])
    {
        $this->nonExpressedNodes = $nonExpressedNodes;
        return $this;
    }

    /**
     * Get the organism used for the current analysis
     *
     * @return string
     */
    public function getOrganism()
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
    public function setOrganism($organism = 'hsa')
    {
        $this->organism = $organism;
        return $this;
    }

    /**
     * Get the customized seed used for the random number generator
     *
     * @return null|integer
     */
    public function getSeed()
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
    public function setSeed($seed = null)
    {
        $this->seed = $seed;
        return $this;
    }

    /**
     * Returns the working directory for this analysis
     *
     * @return string
     */
    public function getWorkingDirectory()
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
    public function setWorkingDirectory($workingDirectory)
    {
        if ($workingDirectory instanceof Job) {
            $workingDirectory = $workingDirectory->getJobDirectory();
        }
        $workingDirectory = rtrim($workingDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->workingDirectory = $workingDirectory;
        return $this;
    }

    /**
     * Get the output filename after the analysis was performed
     *
     * @return string
     */
    public function getOutputFilename()
    {
        return $this->outputFilename;
    }

    /**
     * Build the input file for SIMPATHY, the command line argument, and returns its name
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function buildInputFile(array &$resultArray)
    {
        if (empty($this->simulationParameters)) {
            throw new LauncherException('You must specify at least one simulation parameter');
        }
        $inputFile = $this->workingDirectory . Utils::tempFilename('phensim_input', 'tsv');
        $fp = @fopen($inputFile, 'w');
        if (!$fp) {
            throw new LauncherException('Unable to create SIMPATHY input file');
        }
        foreach ($this->simulationParameters as $parameter => $type) {
            @fwrite($fp, $parameter . "\t" . $type . PHP_EOL);
        }
        @fclose($fp);
        $this->buildParameter($inputFile, self::SIMPATHY_INPUT, $resultArray);
        $this->inputFiles[] = $inputFile;
    }

    /**
     * Build the file containing non-expressed nodes.
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function buildNonExpressedFile(array &$resultArray)
    {
        if (empty($this->nonExpressedNodes)) return;
        $nonExpFile = $this->workingDirectory . Utils::tempFilename('phensim_nonexp', 'txt');
        $fp = @fopen($nonExpFile, 'w');
        if (!$fp) {
            throw new LauncherException('Unable to create SIMPATHY non-expressed nodes file');
        }
        foreach ($this->nonExpressedNodes as $node) {
            @fwrite($fp, $node . PHP_EOL);
        }
        @fclose($fp);
        $this->buildParameter($nonExpFile, self::SIMPATHY_NON_EXPRESSED, $resultArray);
        $this->inputFiles[] = $nonExpFile;
    }

    /**
     * Build the output file name and command line argument
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function buildOutputFile(array &$resultArray)
    {
        $outputFile = $this->workingDirectory . Utils::tempFilename('phensim_output', 'tsv');
        $this->buildParameter($outputFile, self::SIMPATHY_OUTPUT, $resultArray);
        $this->outputFilename = $outputFile;
    }

    /**
     * Build a list parameter in the command line
     *
     * @param array  $parameter
     * @param string $pattern
     * @param array  $resultArray
     *
     * @return void
     */
    private function buildListParameter(array $parameter, $pattern, array &$resultArray)
    {
        if (!empty($parameter)) {
            $resultArray[] = sprintf($pattern, escapeshellarg(implode(',', $parameter)));
        }
    }

    /**
     * Build a parameter in the command line
     *
     * @param mixed  $value
     * @param string $pattern
     * @param array  $resultArray
     *
     * @return void
     */
    private function buildParameter($value, $pattern, array &$resultArray)
    {
        if (!empty($value)) {
            $resultArray[] = sprintf($pattern, (is_numeric($value)) ? $value : escapeshellarg($value));
        }
    }

    /**
     * Build the -p parameter for the command line
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function buildEnricherParameters(array &$resultArray)
    {
        if (!empty($this->enricherParameters)) {
            $parameters = [];
            foreach ($this->enricherParameters as $param => $value) {
                $parameters[] = sprintf(self::SIMPATHY_ENRICHER_PARAMETER, $param, escapeshellarg($value));
            }
            $resultArray[] = implode(' ', $parameters);
        }
    }

    /**
     * Build the command to run phensim using parameters provided by the user
     *
     * @return string
     */
    private function buildCommandLine()
    {
        $algorithm = self::SIMPATHY;
        $parameters = [];
        $this->buildInputFile($parameters);
        $this->buildNonExpressedFile($parameters);
        $this->buildOutputFile($parameters);
        $this->buildListParameter($this->enrichers, self::SIMPATHY_ENRICHERS, $parameters);
        $this->buildEnricherParameters($parameters);
        $this->buildParameter($this->epsilon, self::SIMPATHY_EPSILON, $parameters);
        $this->buildParameter($this->simulationIterations, self::SIMPATHY_ITERATIONS, $parameters);
        $this->buildParameter($this->miRNAEnrichmentEvidence, self::SIMPATHY_MIRNA_ENRICHMENT_EVIDENCE, $parameters);
        $this->buildParameter($this->organism, self::SIMPATHY_ORGANISM, $parameters);
        $this->buildParameter($this->seed, self::SIMPATHY_SEED, $parameters);
        $parameters[] = self::SIMPATHY_VERBOSE;
        return sprintf(self::MITHRIL_EXEC, resource_path(self::MITHRIL_JAR), $algorithm, implode(' ', $parameters));
    }

    /**
     * Run SIMPATHY.
     *
     * @return array|bool
     * @throws CommandException
     * @throws LauncherException
     */
    public function run()
    {
        $command = $this->buildCommandLine();
        $commandOutput = null;
        $result = null;
        try {
            $result = Utils::runCommand($command, $commandOutput);
        } catch (CommandException $e) {
            Utils::mapCommandException('phensim', $e, [
                101 => 'Invalid input file: file does not exist.',
                102 => 'Invalid species: species not found.',
                103 => (is_array($commandOutput)) ? array_pop($commandOutput) : 'Unknown error',
            ]);
        }
        if (!file_exists($this->outputFilename)) {
            throw new LauncherException('Unable to create output file');
        }
        return ($result) ? $commandOutput : false;
    }

    /**
     * Returns the command line used to run SIMPATHY
     *
     * @return string
     */
    public function getCommandLine()
    {
        return $this->buildCommandLine();
    }


    /**
     * Delete all temporary input files for SIMPATHY
     *
     * @return bool
     */
    public function deleteInputFiles()
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
