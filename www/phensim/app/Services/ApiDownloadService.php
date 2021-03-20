<?php

namespace App\Services;

use App\Models\Simulation;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiDownloadService
{

    /**
     * @var \App\Models\Simulation
     */
    private Simulation $simulation;

    /**
     * ApiDownloadService constructor.
     *
     * @param  \App\Models\Simulation  $simulation
     */
    public function __construct(Simulation $simulation)
    {
        $this->simulation = $simulation;
    }

    /**
     * Get the filename to return to the download response
     *
     * @param  string  $type
     * @param  string  $extension
     *
     * @return string
     */
    private function getDownloadFilename(string $type, string $extension = '.txt'): string
    {
        return $this->simulation->id . '-' . Str::slug($this->simulation->name) . '-' . $type . $extension;
    }

    /**
     * Make the download URL for a certain type
     *
     * @param  string  $type
     *
     * @return string|null
     */
    public function downloadUrl(string $type): ?string
    {
        if (!$this->check($type)) {
            return null;
        }

        return route('api.simulations.download', [$this->simulation, $type]);
    }

    /**
     * Check if a file from the simulation can be downloaded
     *
     * @param  string  $type
     *
     * @return bool
     */
    public function check(string $type): bool
    {
        $methodName = 'check' . Str::camel($type);

        return method_exists($this, $methodName) && $this->$methodName();
    }

    /**
     * Download a file from the simulation
     *
     * @param  string  $type
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $type): BinaryFileResponse|StreamedResponse
    {
        $methodName = 'download' . Str::camel($type);
        abort_unless(method_exists($this, $methodName), 500, 'Invalid method name');

        return $this->$methodName();
    }

    /**
     * Dummy method - Input Parameters are always available
     *
     * @return bool
     */
    public function checkInputParameters(): bool
    {
        return true;
    }

    /**
     * Download the input parameters
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadInputParameters(): BinaryFileResponse|StreamedResponse
    {
        $filename = $this->getDownloadFilename('input-parameters');
        if ($this->simulation->input_parameters_file !== null) {
            return response()->download($this->simulation->input_parameters_file, $filename);
        }

        return response()->streamDownload(
            function () {
                $parameters = $this->simulation->getParameter('inputParameters');
                foreach ($parameters as $type => $nodes) {
                    echo implode(PHP_EOL, array_map(static fn($n) => $n . "\t" . $type, $nodes)) . PHP_EOL;
                }
            },
            $filename
        );
    }

    /**
     * Checks if this simulation has an enrichment database
     *
     * @return bool
     */
    public function checkEnrichmentDatabase(): bool
    {
        return $this->simulation->enrichment_database_file !== null;
    }

    /**
     * Download the enrichment database
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadEnrichmentDatabase(): BinaryFileResponse
    {
        abort_if($this->simulation->enrichment_database_file === null, 404);

        return response()->download($this->simulation->enrichment_database_file, $this->getDownloadFilename('enrichment-database'));
    }

    /**
     * Checks if this simulation has node types
     *
     * @return bool
     */
    public function checkNodeTypes(): bool
    {
        return $this->simulation->node_types_file !== null;
    }

    /**
     * Download the node types
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadNodeTypes(): BinaryFileResponse
    {
        abort_if($this->simulation->node_types_file === null, 404);

        return response()->download($this->simulation->node_types_file, $this->getDownloadFilename('node-types'));
    }

    /**
     * Checks if this simulation has edge types
     *
     * @return bool
     */
    public function checkEdgeTypes(): bool
    {
        return $this->simulation->edge_types_file !== null;
    }

    /**
     * Download the edge types
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadEdgeTypes(): BinaryFileResponse
    {
        abort_if($this->simulation->edge_types_file === null, 404);

        return response()->download($this->simulation->edge_types_file, $this->getDownloadFilename('edge-types'));
    }

    /**
     * Checks if this simulation has edge subtypes
     *
     * @return bool
     */
    public function checkEdgeSubtypes(): bool
    {
        return $this->simulation->edge_subtypes_file !== null;
    }

    /**
     * Download the edge subtypes
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadEdgeSubtypes(): BinaryFileResponse
    {
        abort_if($this->simulation->edge_subtypes_file === null, 404);

        return response()->download($this->simulation->edge_subtypes_file, $this->getDownloadFilename('edge-subtypes'));
    }

    /**
     * Checks if this simulation has non-expressed nodes
     *
     * @return bool
     */
    public function checkNonExpressedNodes(): bool
    {
        $nodes = $this->simulation->getParameter('nonExpressed');

        return $this->simulation->non_expressed_nodes_file !== null || !empty($nodes);
    }

    /**
     * Download the non-expressed nodes
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadNonExpressedNodes(): BinaryFileResponse|StreamedResponse
    {
        $nodes = $this->simulation->getParameter('nonExpressed');
        abort_if($this->simulation->non_expressed_nodes_file === null && empty($nodes), 404);
        $filename = $this->getDownloadFilename('non-expressed-nodes');
        if ($this->simulation->non_expressed_nodes_file !== null) {
            return response()->download($this->simulation->non_expressed_nodes_file, $filename);
        }

        return response()->streamDownload(
            function () use ($nodes) {
                echo implode(PHP_EOL, $nodes);
            },
            $filename
        );
    }

    /**
     * Checks if this simulation has knocked-out nodes
     *
     * @return bool
     */
    public function checkRemovedNodes(): bool
    {
        $nodes = $this->simulation->getParameter('remove');

        return !empty($nodes);
    }

    /**
     * Download the knocked-out nodes
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadRemovedNodes(): StreamedResponse
    {
        $nodes = $this->simulation->getParameter('remove');
        abort_if(empty($nodes), 404);
        $filename = $this->getDownloadFilename('removed-nodes');

        return response()->streamDownload(
            function () use ($nodes) {
                echo implode(PHP_EOL, $nodes);
            },
            $filename
        );
    }

    /**
     * Checks if this simulation has any output file
     *
     * @return bool
     */
    public function checkOutput(): bool
    {
        return $this->simulation->output_file !== null;
    }

    /**
     * Download the output file
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadOutput(): BinaryFileResponse
    {
        abort_if($this->simulation->output_file === null, 404);

        return response()->download($this->simulation->output_file, $this->getDownloadFilename('output'));
    }

    /**
     * Checks if this simulation has any pathway output file
     *
     * @return bool
     */
    public function checkPathwayOutput(): bool
    {
        return $this->simulation->pathway_output_file !== null;
    }

    /**
     * Download the pathway output file
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPathwayOutput(): BinaryFileResponse
    {
        abort_if($this->simulation->pathway_output_file === null, 404);

        return response()->download($this->simulation->pathway_output_file, $this->getDownloadFilename('pathway-output'));
    }

    /**
     * Checks if this simulation has any nodes output file
     *
     * @return bool
     */
    public function checkNodesOutput(): bool
    {
        return $this->simulation->nodes_output_file !== null;
    }

    /**
     * Download the nodes output file
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadNodesOutput(): BinaryFileResponse
    {
        abort_if($this->simulation->nodes_output_file === null, 404);

        return response()->download($this->simulation->nodes_output_file, $this->getDownloadFilename('nodes-output'));
    }

}
