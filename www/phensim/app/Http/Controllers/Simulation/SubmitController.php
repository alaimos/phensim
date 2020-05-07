<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Jobs\DispatcherJob;
use App\Models\Job;
use App\Models\Node;
use App\Models\Organism;
use App\PHENSIM\Constants;
use App\PHENSIM\Launcher;
use App\PHENSIM\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator as RealValidator;
use Illuminate\View\View;
use Validator;

class SubmitController extends Controller
{

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Render Simple Submission Form
     *
     * @return \Illuminate\View\View
     */
    public function submitSimple(): View
    {
        return view(
            'simulation.submit_simple',
            [
                'organisms' => Organism::pluck('name', 'accession'),
            ]
        );
    }

    /**
     * Read the list of nodes from a file
     *
     * @param string $fileName
     *
     * @return array
     */
    private function readSimpleNodesFile(string $fileName): array
    {
        $content = file_get_contents($fileName);

        return array_filter(array_map("trim", preg_split('/[\s,]+/', $content)));
    }

    /**
     * Read one list of nodes from the current request
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $arrayField
     * @param string                   $fileField
     *
     * @return array
     */
    private function prepareSimpleNodesList(Request $request, string $arrayField, string $fileField): array
    {
        $nodes = [];
        if ($request->has($arrayField) && is_array($o = $request->get($arrayField))) {
            $nodes = $o;
        } else {
            $file = $request->file($fileField);
            if ($file !== null && $file->isValid()) {
                $nodes = $this->readSimpleNodesFile($file->path());
            }
        }

        return array_filter(array_unique($nodes));
    }

    /**
     * Read lists of simulated nodes
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    private function prepareSimpleSimulationList(Request $request): array
    {
        $inputArray = [];
        foreach ($this->prepareSimpleNodesList($request, 'overexp-nodes', 'overexp-file') as $n) {
            $inputArray[$n] = Launcher::OVEREXPRESSION;
        }
        foreach ($this->prepareSimpleNodesList($request, 'underexp-nodes', 'underexp-file') as $n) {
            $inputArray[$n] = Launcher::UNDEREXPRESSION;
        }

        return $inputArray;
    }

    /**
     * Save and queues simple simulation job
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doSubmitSimple(Request $request): RedirectResponse
    {
        Validator::extendImplicit(
            'hasNodes',
            function ($attribute, $value, $parameters, RealValidator $validator) {
                $allFields = ['overexp-nodes', 'underexp-nodes'];
                $allFiles = ['overexp-file', 'underexp-file'];
                $data = $validator->getData();
                $ok = false;
                foreach ($allFields as $field) {
                    if ($field !== $attribute) {
                        $val = Arr::get($data, $field);
                        $ok = $ok || (!empty($val) && is_array($val));
                    }
                }
                foreach ($allFiles as $field) {
                    if ($field !== $attribute) {
                        $val = Arr::get($data, $field);
                        $ok = $ok || (!empty($val) && $val instanceof UploadedFile);
                    }
                }
                if (!$ok && in_array($attribute, $allFields, true) && !empty($value) && is_array($value)) {
                    $ok = true;
                }
                if (!$ok && in_array($attribute, $allFiles) && !empty($value) && $value instanceof UploadedFile) {
                    $ok = true;
                }

                return $ok;
            }
        );
        $this->validate(
            $request,
            [
                'organism'       => 'required|exists:organisms,accession',
                'overexp-nodes'  => 'hasNodes',
                'overexp-file'   => 'hasNodes',
                'underexp-nodes' => 'hasNodes',
                'underexp-file'  => 'hasNodes',
            ],
            [
                'has_nodes' => 'You must specify overexpressed or underexpressed nodes.',
            ]
        );
        $name = trim($request->get('job_name', ''));
        $organism = $request->get('organism', 'hsa');
        $nodes = $this->prepareSimpleSimulationList($request);
        $nonExp = $this->prepareSimpleNodesList($request, 'nonexp-nodes', 'nonexp-file');
        $epsilon = (float)$request->get('epsilon', 0.001);
        $seed = $request->get('random-seed');
        $enrich = in_array($request->get('enrich-mirnas'), ['on', 1, 'On', 'ON'], false);
        $job = Job::buildJob(
            Constants::SIMULATION_JOB,
            [
                'organism'             => $organism,
                'simulationParameters' => $nodes,
                'nonExpressed'         => $nonExp,
                'dbFilter'             => null,
                'epsilon'              => $epsilon,
                'seed'                 => $seed,
                'enrichMirs'           => $enrich,
                'enrichDb'             => null,
                'nodeTypes'            => null,
                'edgeTypes'            => null,
                'edgeSubTypes'         => null,
            ],
            [],
            $name
        );
        $this->dispatch(new DispatcherJob($job->id));

        return redirect()->route('user-home');
    }

    /**
     * Render the Advanced Submission form
     *
     * @return \Illuminate\View\View
     */
    public function submitEnriched(): View
    {
        return view(
            'simulation.submit_enriched',
            [
                'organisms' => Organism::pluck('name', 'accession'),
            ]
        );
    }

    /**
     * Extends validators
     */
    private static function extendValidatorsEnriched()
    {
        Validator::extendImplicit(
            'validDb',
            static function ($attribute, $value, $parameters, RealValidator $validator) {
                if ($value instanceof UploadedFile) {
                    return Utils::checkDbFile($value->path());
                }

                return false;
            }
        );
        Validator::extendImplicit(
            'validParameters',
            static function ($attribute, $value, $parameters, RealValidator $validator) {
                if ($value instanceof UploadedFile) {
                    return Utils::checkInputFile($value->path());
                }

                return false;
            }
        );
        Validator::extend(
            'validNodeType',
            static function ($attribute, $value, $parameters, RealValidator $validator) {
                if ($value instanceof UploadedFile) {
                    return Utils::checkNodeTypeFile($value->path());
                }

                return true;
            }
        );
        Validator::extend(
            'validEdgeType',
            static function ($attribute, $value, $parameters, RealValidator $validator) {
                if ($value instanceof UploadedFile) {
                    return Utils::checkEdgeTypeFile($value->path());
                }

                return true;
            }
        );
        Validator::extend(
            'validEdgeSubType',
            static function ($attribute, $value, $parameters, RealValidator $validator) {
                if ($value instanceof UploadedFile) {
                    return Utils::checkEdgeSubTypeFile($value->path());
                }

                return true;
            }
        );
    }

    /**
     * Prepare an uploaded file
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Job          $job
     * @param string                   $key
     *
     * @return null|string
     */
    private function prepareUploadedFile(Request $request, Job $job, string $key): ?string
    {
        $file = $request->file($key);
        if ($file !== null && ($file instanceof UploadedFile)) {
            $moved = $file->move($job->getJobDirectory(), str_replace('-', '_', $key));

            return $moved->getPathname();
        }

        return null;
    }

    /**
     * Submit the enriched analysis
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doSubmitEnriched(Request $request): RedirectResponse
    {
        self::extendValidatorsEnriched();
        $this->validate(
            $request,
            [
                'organism'             => 'required|exists:organisms,accession',
                'simulation-input'     => 'required|file|validParameters',
                'enrich-db'            => 'sometimes|file|validDb',
                'nonexp-nodes'         => 'sometimes|file',
                'custom-node-types'    => 'sometimes|file|validNodeType',
                'custom-edge-types'    => 'sometimes|file|validEdgeType',
                'custom-edge-subtypes' => 'sometimes|file|validEdgeSubType',
                'epsilon'              => 'sometimes|numeric',
            ],
            [
                'valid_db'            => 'You must upload a valid enrichment database file',
                'valid_parameters'    => 'You must upload a valid simulation parameters file',
                'valid_node_type'     => 'You must upload a valid custom node type file',
                'valid_edge_type'     => 'You must upload a valid custom edge type file',
                'valid_edge_sub_type' => 'You must upload a valid custom edge subtype file',
            ]
        );
        $nonExp = [];
        if ((($file = $request->file('nonexp-nodes')) !== null) && $file->isValid()) {
            $nonExp = array_filter(array_unique($this->readSimpleNodesFile($file->path())));
        }
        $name = trim($request->get('job_name', ''));
        $job = Job::buildJob(
            Constants::SIMULATION_JOB,
            [
                'organism'             => $request->get('organism', 'hsa'),
                'simulationParameters' => Utils::readInputFile($request->file('simulation-input')->path()),
                'nonExpressed'         => $nonExp,
                'dbFilter'             => $request->get('db-filter'),
                'epsilon'              => (float)$request->get('epsilon', 0.001),
                'seed'                 => $request->get('random-seed'),
                'enrichMirs'           => in_array($request->get('enrich-mirnas'), ['on', 1, 'On', 'ON'], false),
            ],
            [],
            $name
        );
        $job->addParameters(
            [
                'enrichDb'     => $this->prepareUploadedFile($request, $job, 'enrich-db'),
                'nodeTypes'    => $this->prepareUploadedFile($request, $job, 'custom-node-types'),
                'edgeTypes'    => $this->prepareUploadedFile($request, $job, 'custom-edge-types'),
                'edgeSubTypes' => $this->prepareUploadedFile($request, $job, 'custom-edge-subtypes'),
            ]
        );
        $job->save();
        $this->dispatch(new DispatcherJob($job->id));

        return redirect()->route('user-home');
    }

    /**
     * Handles searching, pagination, and listing of nodes
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listNodes(Request $request): LengthAwarePaginatorContract
    {
        /** @var Organism $organism */
        $organism = Organism::whereAccession($request->get('organism'))->first();
        $perPage = (int)$request->get('perPage', 30);
        if ($organism !== null && $organism->exists) {
            $q = $request->get('q');
            $query = Node::whereOrganismId($organism->id)->where(
                static function (Builder $query) use ($q) {
                    $query->where('nodes.accession', 'like', '%' . $q . '%')
                          ->orWhere('nodes.name', 'like', '%' . $q . '%')
                          ->orWhere('nodes.aliases', 'like', '%' . $q . '%');
                }
            )->orderBy('nodes.accession');

            return $query->paginate($perPage, ['accession', 'name']);
        }

        return new LengthAwarePaginator([], 0, $perPage);
    }

}
