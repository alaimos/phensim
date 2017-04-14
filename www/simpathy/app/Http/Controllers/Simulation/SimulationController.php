<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\SIMPATHY\Reader;
use Datatables;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SimulationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Redirect to the real job viewer
     *
     * @param \App\Models\Job $job
     *
     * @return \Illuminate\View\View
     * @throws \Exception
     */
    public function viewSimulation(Job $job): View
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        return view('jobs.simulation_job.pathway_list', [
            'job' => $job,
        ]);
    }

    /**
     * Prepare data for the jobs table
     *
     * @param \App\Models\Job $job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function simulationPathwayListData(Job $job): JsonResponse
    {
        /** @var \Yajra\Datatables\Engines\CollectionEngine $table */
        $table = Datatables::of((new Reader($job))->readPathways());
        $table->addColumn('action', function (array $data) use ($job) {
            return view('jobs.simulation_job.pathway_list_action_column', [
                'job'  => $job,
                'data' => $data,
            ])->render();
        })->rawColumns(['action']);
        return $table->make(true);
    }


}
