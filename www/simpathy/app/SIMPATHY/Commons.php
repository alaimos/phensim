<?php

namespace App\SIMPATHY;

use App\Exceptions\CommandException;

final class Commons
{
    const SIMPATHY = 'simpathy';
    const MERGED_SIMPATHY = 'merged-simpathy';
    const MERGED_SIMPATHY_EXCLUDE_VALUES = [
        'Endocrine and metabolic diseases',
        'Neurodegenerative diseases',
        'Human Diseases',
        'Immune diseases',
        'Infectious diseases',
        'Cardiovascular diseases',
    ];
    const MERGED_SIMPATHY_EXCLUDE = '-exclude-categories %s';
    const SIMPATHY_ENRICHERS = '-e %s';
    const SIMPATHY_ENRICHER_PARAMETER = '-p %s=%s';
    const SIMPATHY_EPSILON = '-epsilon %.10f';
    const SIMPATHY_INPUT = '-i %s';
    const SIMPATHY_ITERATIONS = '-number-of-iterations %d';
    const SIMPATHY_MIRNA_ENRICHMENT_EVIDENCE = '-enrichment-evidence-type %s';
    const SIMPATHY_NON_EXPRESSED = '-non-expressed-nodes %s';
    const SIMPATHY_ORGANISM = '-organism %s';
    const SIMPATHY_OUTPUT = '-o %s';
    const SIMPATHY_SEED = '-seed %d';
    const SIMPATHY_SUPPORTED_EVIDENCES = ['STRONG', 'WEAK', 'PREDICTION'];

    /**
     * Run MITHrIL 2 to extract SubStructures
     *
     * @param \App\Models\Disease $disease
     * @param string              $outputFile
     * @param array|null          $nodesOfInterest
     * @param float               $maxPValuePathways
     * @param float               $maxPValueNoIs
     * @param float               $maxPValueNodes
     * @param float               $maxPValuePaths
     * @param int                 $minNumberOfNodes
     * @param bool                $backward
     * @param array|null          $commandOutput
     *
     * @return bool
     */
    public static function exportSubStructures(Disease $disease, $outputFile, array $nodesOfInterest = null,
        $maxPValuePathways = 0.01, $maxPValueNoIs = 0.025, $maxPValueNodes = 0.05, $maxPValuePaths = 1e-5,
        $minNumberOfNodes = 5, $backward = false, array &$commandOutput = null)
    {
        $hasNoIs = false;
        if ($nodesOfInterest !== null && !empty($nodesOfInterest)) {
            $hasNoIs = true;
            $nodesOfInterest = array_map(function ($e) {
                if ($e instanceof Node) {
                    return $e->accession;
                }
                return $e;
            }, $nodesOfInterest);
            $nodesOfInterest = escapeshellarg(implode(',', $nodesOfInterest));
        }
        $optionalCommandLine = [];
        $optionalCommandLine[] = ($hasNoIs) ? sprintf(self::MITHRIL_NODES_OF_INTERESTS, $nodesOfInterest) : '';
        $optionalCommandLine[] = ($backward) ? self::MITHRIL_BACKWARD_VISIT : '';
        $optionalCommandLine = implode(' ', $optionalCommandLine);
        $inputFile = escapeshellarg(self::getMithrilInputPath($disease));
        $outputFile = escapeshellarg($outputFile);
        $dataFile = escapeshellcmd(resource_path(self::DATA_FILE));
        $excluded = escapeshellarg(implode(',', self::EXCLUDED_PATHWAY_CATEGORIES));
        $parameters = sprintf(self::MITHRIL_EXPORT_PARAMETERS, $inputFile, $outputFile, $maxPValuePaths,
            $minNumberOfNodes, $maxPValuePathways, $maxPValueNodes, $maxPValueNoIs, $excluded, $dataFile,
            $optionalCommandLine);
        $command = sprintf(self::MITHRIL_EXEC, resource_path(self::MITHRIL_JAR), 'exportstructs', $parameters);
        return Utils::runCommand($command, $commandOutput);
    }
}