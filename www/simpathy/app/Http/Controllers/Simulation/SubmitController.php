<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Jobs\DispatcherJob;
use App\Models\Job;
use App\Models\Node;
use App\Models\Organism;
use App\SIMPATHY\Launcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator as RealValidator;
use Illuminate\View\View;
use Validator;

class SubmitController extends Controller
{

    /**
     * @return \Illuminate\View\View
     */
    public function submitSimple(): View
    {
        return view('simulation.submit_simple', [
            'organisms' => Organism::pluck('name', 'accession'),
        ]);
    }

    /**
     * Read the list of nodes from a file
     *
     * @param string $fileName
     *
     * @return array
     */
    private function readNodesFile(string $fileName): array
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
    private function prepareNodesList(Request $request, string $arrayField, string $fileField): array
    {
        $nodes = [];
        if ($request->has($arrayField) && is_array($o = $request->get($arrayField))) {
            $nodes = $o;
        } else {
            $file = $request->file($fileField);
            if ($file != null && $file->isValid()) {
                $nodes = $this->readNodesFile($file->path());
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
    public function prepareSimulationList(Request $request): array
    {
        return [
            Launcher::OVEREXPRESSION  => $this->prepareNodesList($request, 'overexp-nodes', 'overexp-file'),
            Launcher::UNDEREXPRESSION => $this->prepareNodesList($request, 'underexp-nodes', 'underexp-file'),
        ];
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
        Validator::extendImplicit('hasNodes', function ($attribute, $value, $parameters, RealValidator $validator) {
            $allFields = ['overexp-nodes', 'underexp-nodes'];
            $allFiles = ['overexp-file', 'underexp-file'];
            $data = $validator->getData();
            $ok = false;
            foreach ($allFields as $field) {
                if ($field != $attribute) {
                    $val = Arr::get($data, $field);
                    $ok = $ok || (!empty($val) && is_array($val));
                }
            }
            foreach ($allFiles as $field) {
                if ($field != $attribute) {
                    $val = Arr::get($data, $field);
                    $ok = $ok || (!empty($val) && $val instanceof UploadedFile);
                }
            }
            if (!$ok && in_array($attribute, $allFields) && !empty($value) && is_array($value)) $ok = true;
            if (!$ok && in_array($attribute, $allFiles) && !empty($value) && $value instanceof UploadedFile) $ok = true;
            return $ok;
        });
        $this->validate($request, [
            'organism'       => 'required|exists:organisms,accession',
            'overexp-nodes'  => 'hasNodes',
            'overexp-file'   => 'hasNodes',
            'underexp-nodes' => 'hasNodes',
            'underexp-file'  => 'hasNodes',
        ], [
            'has_nodes' => 'You must specify overexpressed or underexpressed nodes.',
        ]);
        $organism = $request->get('organism', 'hsa');
        $nodes = $this->prepareSimulationList($request);
        $nonExp = $this->prepareNodesList($request, 'nonexp-nodes', 'nonexp-file');
        $epsilon = doubleval($request->get('epsilon', 0.001));
        $seed = $request->get('random-seed');
        $enrich = in_array($request->get('enrich-mirnas'), ['on', 1, 'On', 'ON']);
        $jobParameters = [
            'organism'     => $organism,
            'nodes'        => $nodes,
            'nonExpressed' => $nonExp,
            'epsilon'      => $epsilon,
            'seed'         => $seed,
            'enrich'       => $enrich,
        ];
        $jobKey = Job::computeKey('simple_simulation', $jobParameters, \Auth::id());
        /** @var Job $job */
        $job = Job::whereJobKey($jobKey)->first();
        if ($job === null) {
            $job = Job::create([
                'user_id'        => \Auth::id(),
                'job_type'       => 'simple_simulation',
                'job_status'     => Job::QUEUED,
                'job_parameters' => $jobParameters,
                'job_data'       => [],
                'job_log'        => '',
            ]);
            $this->dispatch(new DispatcherJob($job->id));
        }
        return redirect()->route('user-home');
    }

    public function submitEnriched(): View
    {
        return view('simulation.submit_enriched', [

        ]);
    }

    /**
     * Handles searching, pagination, and listing of disease-specific NoIs
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Http\JsonResponse
     */
    public function listNodes(Request $request)
    {
        /** @var Organism $organism */
        $organism = Organism::whereAccession($request->get('organism'))->first();
        $response = [
            'total'        => 0,
            'per_page'     => 0,
            'current_page' => 0,
            'last_page'    => 0,
            'data'         => [],
        ];
        if ($organism !== null && $organism->exists) {
            $q = $request->get('q');
            $perPage = (int)$request->get('perPage', 30);
            $query = Node::whereOrganismId($organism->id)->where(function (Builder $query) use ($q) {
                $query->where('nodes.accession', 'like', '%' . $q . '%')
                      ->orWhere('nodes.name', 'like', '%' . $q . '%')
                      ->orWhere('nodes.aliases', 'like', '%' . $q . '%');
            })->orderBy('nodes.accession');
            return $query->paginate($perPage, ['accession', 'name']);
        }
        return response()->json($response);
    }

}
