<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Models\Job;
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
        throw new \Exception("TEMPORANEO");
    }

}
