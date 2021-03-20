<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SimulationRequest;
use App\Http\Resources\SimulationResource;
use App\Models\Organism;
use App\Models\Simulation;
use App\PHENSIM\Launcher;
use App\PHENSIM\Utils;
use App\Rules\InputFileRule;
use App\Services\ApiDownloadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SimulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request): Response|AnonymousResourceCollection
    {
        $perPage = (int)$request->get('per_page', 10);

        return SimulationResource::collection(
            Simulation::visible()->with('organism')->paginate($perPage)->appends($request->input())
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Api\SimulationRequest  $request
     *
     * @return \App\Http\Resources\SimulationResource
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException|\App\Exceptions\FileSystemException
     */
    public function store(SimulationRequest $request): SimulationResource
    {
        $validData = $request->validated();

        $simulation = Simulation::create(
            [
                'name'        => $validData['name'],
                'user_id'     => auth()->id(),
                'organism_id' => Organism::where('accession', $validData['organism'])->firstOrFail()->id,
                'status'      => Simulation::READY,
                'parameters'  => [
                    'epsilon'        => $validData['epsilon'] ?? 0.001,
                    'seed'           => $validData['seed'] ?? null,
                    'fdr'            => $validData['fdr'] ?? Launcher::FDR_BH,
                    'reactome'       => $validData['reactome'] ?? false,
                    'fast'           => $validData['fast'] ?? true,
                    'enrichMiRNAs'   => $validData['miRNAs'] ?? true,
                    'miRNAsEvidence' => $validData['miRNAsEvidence'] ?? Launcher::EVIDENCE_STRONG,
                    'remove'         => $validData['nodes']['knockout'] ?? [],
                    'filter'         => $validData['filter'] ?? null,
                ],
            ]
        );
        $jobDirRelative = $simulation->jobDirectoryRelative();
        $jobDirAbsolute = $simulation->jobDirectory() . DIRECTORY_SEPARATOR;
        if ($request->hasFile('simulationParametersFile')) {
            $file = $request->file('simulationParametersFile');
            $filename = basename($file->store($jobDirRelative));
            $simulation->input_parameters_file = $jobDirAbsolute . $filename;
        } else {
            $simulation->setParameter(
                'inputParameters',
                [
                    Launcher::OVEREXPRESSION  => $validData['nodes']['overExpressed'] ?? [],
                    Launcher::UNDEREXPRESSION => $validData['nodes']['underExpressed'] ?? [],
                ]
            );
        }
        if ($request->hasFile('nonExpressedNodesFile')) {
            $file = $request->file('nonExpressedNodesFile');
            $filename = basename($file->store($jobDirRelative));
            $simulation->non_expressed_nodes_file = $jobDirAbsolute . $filename;
        } else {
            $simulation->setParameter('nonExpressed', $validData['nodes']['nonExpressed'] ?? []);
        }
        if ($request->hasFile('enrichmentDatabaseFile')) {
            $file = $request->file('enrichmentDatabaseFile');
            $filename = basename($file->store($jobDirRelative));
            $simulation->enrichment_database_file = $jobDirAbsolute . $filename;
        }
        if ($request->hasFile('customNodeTypesFile')) {
            $file = $request->file('customNodeTypesFile');
            $filename = basename($file->store($jobDirRelative));
            $simulation->node_types_file = $jobDirAbsolute . $filename;
        }
        if ($request->hasFile('customEdgeTypesFile')) {
            $file = $request->file('customEdgeTypesFile');
            $filename = basename($file->store($jobDirRelative));
            $simulation->edge_types_file = $jobDirAbsolute . $filename;
        }
        if ($request->hasFile('customEdgeSubtypesFile')) {
            $file = $request->file('customEdgeSubtypesFile');
            $filename = basename($file->store($jobDirRelative));
            $simulation->edge_subtypes_file = $jobDirAbsolute . $filename;
        }
        if ($request->hasFile('knockoutNodesFile')) {
            $file = $request->file('knockoutNodesFile');
            $simulation->setParameter(
                'remove',
                array_filter(array_map("trim", explode("\n", $file->get())))
            );
        }
        $simulation->save();
        if ($validData['submit'] ?? false) {
            $simulation->submit();
        }

        return new SimulationResource($simulation);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return \App\Http\Resources\SimulationResource|\Illuminate\Http\Response
     */
    public function show(Simulation $simulation): Response|SimulationResource
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);

        return new SimulationResource($simulation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\FileSystemException
     * @throws \Exception
     */
    public function destroy(Simulation $simulation): JsonResponse
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);

        $simulation->deleteJobDirectory();
        $simulation->delete();

        return response()->json(
            [
                'deleted' => true,
            ]
        );
    }

    /**
     * Download the specified file from the resource
     *
     * @param  \App\Models\Simulation  $simulation
     * @param  string  $type
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function download(Simulation $simulation, string $type): BinaryFileResponse|StreamedResponse
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);
        $downloadService = app()->make(ApiDownloadService::class, [$simulation]);

        return $downloadService->download($type);
    }

    /**
     * Submit or resubmit the specified resource to the job queue
     *
     * @param  \App\Models\Simulation  $simulation
     *
     * @return \App\Http\Resources\SimulationResource
     */
    public function submit(Simulation $simulation): SimulationResource
    {
        abort_if(!auth()->user()->is_admin && $simulation->user_id !== auth()->id(), 403);
        if ($simulation->isReady()) {
            $simulation->submit();
        } elseif ($simulation->isFailed() || ($simulation->isCompleted() && auth()->user()->is_admin)) {
            $simulation->reSubmit();
        }

        return new SimulationResource($simulation);
    }
}
