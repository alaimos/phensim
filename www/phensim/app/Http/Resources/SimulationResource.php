<?php

namespace App\Http\Resources;

use App\Services\ApiDownloadService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SimulationResource
 * @package App\Http\Resources
 * @mixin \App\Models\Simulation
 */
class SimulationResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function toArray($request): array
    {
        $downloadService = app()->make(ApiDownloadService::class, [$this->resource]);

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'status'          => $this->status,
            'readable_status' => $this->readable_status,
            'parameters'      => [
                'fast'           => $this->getParameter('fast'),
                'fdr'            => $this->getParameter('fdr'),
                'epsilon'        => $this->getParameter('epsilon'),
                'seed'           => $this->getParameter('seed'),
                'reactome'       => $this->getParameter('reactome'),
                'enrichMiRNAs'   => $this->getParameter('enrichMiRNAs'),
                'miRNAsEvidence' => $this->getParameter('miRNAsEvidence'),
            ],
            'logs'            => $this->logs,
            'organism'        => $this->organism->accession,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'links'           => [
                'public'              => '',
                'input_parameters'    => $downloadService->downloadUrl('input_parameters'),
                'enrichment_database' => $downloadService->downloadUrl('enrichment_database'),
                'node_types'          => $downloadService->downloadUrl('node_types'),
                'edge_types'          => $downloadService->downloadUrl('edge_types'),
                'edge_subtypes'       => $downloadService->downloadUrl('edge_subtypes'),
                'non_expressed_nodes' => $downloadService->downloadUrl('non_expressed_nodes'),
                'removed_nodes'       => $downloadService->downloadUrl('removed_nodes'),
                'output'              => $downloadService->downloadUrl('output'),
                'pathway_output'      => $downloadService->downloadUrl('pathway_output'),
                'nodes_output'        => $downloadService->downloadUrl('nodes_output'),
                'sbml_output'         => $downloadService->downloadUrl('sbml_output'),
                'sif_output'          => $downloadService->downloadUrl('sif_output'),
            ],
        ];
    }
}
