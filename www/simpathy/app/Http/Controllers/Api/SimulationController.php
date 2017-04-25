<?php

namespace App\Http\Controllers\Api;

use App\Jobs\DispatcherJob;
use App\Models\Job;
use App\SIMPATHY\Constants;
use App\SIMPATHY\Reader;
use App\SIMPATHY\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimulationController extends Controller
{

    public static function provideRoutes(): array
    {
        return [
            '/simulations'                                  => [
                'get'    => ['SimulationController@listSimulations', 'api-simulations-list'],
                'post'   => ['SimulationController@submitSimulation', 'api-submit-simulation'],
                'others' => ['SimulationController@unsupportedMethod'],
            ],
            '/simulations/{job}'                            => [
                'get'    => ['SimulationController@getSimulation', 'api-get-simulation'],
                'others' => ['SimulationController@unsupportedMethod'],
            ],
            '/simulations/{job}/parameters'                 => [
                'get'    => ['SimulationController@getSimulationParameters', 'api-get-simulation-parameters'],
                'others' => ['SimulationController@unsupportedMethod'],
            ],
            '/simulations/{job}/results/raw'                => [
                'get'    => ['SimulationController@getSimulationResultsRaw', 'api-get-simulation-results-raw'],
                'others' => ['SimulationController@unsupportedMethod'],
            ],
            '/simulations/{job}/results/pathways'           => [
                'get'    => ['SimulationController@getSimulationResultsPathways',
                             'api-get-simulation-results-pathways'],
                'others' => ['SimulationController@unsupportedMethod'],
            ],
            '/simulations/{job}/results/pathways/{pathway}' => [
                'get'    => ['SimulationController@getSimulationResultsOnePathway',
                             'api-get-simulation-results-one-pathway'],
                'others' => ['SimulationController@unsupportedMethod'],
            ],
        ];
    }

    /**
     * Get and checks a job from the DB
     *
     * @param mixed $job
     * @param bool  $checkCompleted
     *
     * @return \App\Models\Job
     */
    private function getJob($job, $checkCompleted = true): Job
    {
        Job::setRoute('api-get-simulation');
        $job = Job::find($job);
        if (!$job || !$job->exists || $job->job_type != Constants::SIMULATION_JOB) {
            abort(404, 'An invalid simulation identifier has been provided');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this simulation job');
        }
        if ($checkCompleted && $job->job_status != Job::COMPLETED) {
            abort(500, 'Simulation job not completed. Please retry when status of the job is COMPLETED.');
        }
        return $job;
    }

    /**
     * Lists all simulations
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listSimulations(Request $request): JsonResponse
    {
        Job::setRoute('api-get-simulation');
        return response()->json(Job::listJobs($request->get('status'), Constants::SIMULATION_JOB)->get());
    }

    private function prepareUploadedFile(Request $request, Job $job, string $field, callable $callback)
    {
        $content = $request->get($field);
        if ($content === null) {
            return null;
        }
        $filename = $job->getJobFile(str_replace('-', '_', $field));
        if (file_put_contents($filename, $content) === false) {
            abort(500, 'Unable to write file "' . $field . '"');
        }
        if (!call_user_func($callback, $filename)) {
            abort(422, 'Invalid content of the "' . $field . '" field.');
        }
        return $filename;
    }

    public function submitSimulation(Request $request): JsonResponse
    {
        if (!Job::canBeCreated()) {
            abort(403, 'You are not allowed to run a new simulation');
        }
        $this->validate($request, [
            'organism'         => 'required|exists:organisms,accession',
            'simulation-input' => 'required',
            'epsilon'          => 'sometimes|numeric',
        ]);
        $nonExp = (array)$request->get('nonexp-nodes', []);
        $job = null;
        try {
            $job = Job::buildJob(Constants::SIMULATION_JOB, [
                'organism'     => $request->get('organism', 'hsa'),
                'nonExpressed' => $nonExp,
                'dbFilter'     => $request->get('db-filter'),
                'epsilon'      => doubleval($request->get('epsilon', 0.001)),
                'seed'         => $request->get('random-seed'),
                'enrichMirs'   => in_array($request->get('enrich-mirnas'), ['on', 1, 'On', 'ON']),
                'metaPathway'  => in_array($request->get('meta-pathway'), ['on', 1, 'On', 'ON']),
            ]);
            $simulationInputFile = $this->prepareUploadedFile($request, $job, 'simulation-input', function ($f) {
                return Utils::checkInputFile($f);
            });
            $job->addParameters([
                'simulationParameters' => Utils::readInputFile($simulationInputFile),
                'enrichDb'             => $this->prepareUploadedFile($request, $job, 'enrich-db', function ($f) {
                    return Utils::checkDbFile($f);
                }),
                'nodeTypes'            => $this->prepareUploadedFile($request, $job, 'custom-node-types',
                    function ($f) {
                        return Utils::checkNodeTypeFile($f);
                    }),
                'edgeTypes'            => $this->prepareUploadedFile($request, $job, 'custom-edge-types',
                    function ($f) {
                        return Utils::checkEdgeTypeFile($f);
                    }),
                'edgeSubTypes'         => $this->prepareUploadedFile($request, $job, 'custom-edge-subtypes',
                    function ($f) {
                        return Utils::checkEdgeSubTypeFile($f);
                    }),
            ]);
            @unlink($simulationInputFile);
            $job->save();
            $this->dispatch(new DispatcherJob($job->id));
        } catch (\Exception $e) {
            if ($job !== null) {
                $job->delete();
            }
            throw $e;
        } catch (\Error $e) {
            if ($job !== null) {
                $job->delete();
            }
            throw $e;
        }
        return response()->json($job);
    }

    /**
     * Get one simulation
     *
     * @param $job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSimulation($job): JsonResponse
    {
        return response()->json($this->getJob($job, false));
    }

    /**
     * Get the parameters of one simulation
     *
     * @param $job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSimulationParameters($job): JsonResponse
    {
        $job = $this->getJob($job, false);
        $params = $job->job_parameters;
        if ($params['enrichDb'] !== null && file_exists($params['enrichDb'])) {
            $params['enrichDb'] = file_get_contents($params['enrichDb']);
        }
        if ($params['nodeTypes'] !== null && file_exists($params['nodeTypes'])) {
            $params['nodeTypes'] = file_get_contents($params['nodeTypes']);
        }
        if ($params['edgeTypes'] !== null && file_exists($params['edgeTypes'])) {
            $params['edgeTypes'] = file_get_contents($params['edgeTypes']);
        }
        if ($params['edgeSubTypes'] !== null && file_exists($params['edgeSubTypes'])) {
            $params['edgeSubTypes'] = file_get_contents($params['edgeSubTypes']);
        }
        return response()->json($params);
    }

    /**
     * Get the raw results of one simulation
     *
     * @param $job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSimulationResultsRaw($job): JsonResponse
    {
        $job = $this->getJob($job);
        $reader = new Reader($job);
        return response()->json([
            'output' => file_get_contents($reader->getOutputFilename()),
        ]);
    }

    /**
     * Get the list of pathways for one simulation job
     *
     * @param $job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSimulationResultsPathways($job): JsonResponse
    {
        $job = $this->getJob($job);
        $reader = new Reader($job);
        return response()->json($reader->readPathwaysList());
    }

    /**
     * Get the list of nodes for one simulation job
     *
     * @param $job
     * @param $pathway
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSimulationResultsOnePathway($job, $pathway): JsonResponse
    {
        $job = $this->getJob($job);
        $reader = new Reader($job);
        return response()->json($reader->readPathway($pathway));
    }

}
