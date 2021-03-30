<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Datatables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        return view('home', [
            'queuedJobs'     => Job::listJobs(Job::QUEUED)->count(),
            'processingJobs' => Job::listJobs(Job::PROCESSING)->count(),
            'completedJobs'  => Job::listJobs(Job::COMPLETED)->count(),
            'failedJobs'     => Job::listJobs(Job::FAILED)->count(),
        ]);
    }

    /**
     * Return a job data
     *
     * @param Job $job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function jobLog(Job $job): JsonResponse
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        return response()->json($job->toArray());
    }

    /**
     * Prepare data for the jobs table
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function jobsData(): JsonResponse
    {
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(Job::listJobs());
        $table->editColumn('job_type', function (Job $job) {
            return ucwords(str_replace(['-', '_'], ' ', $job->job_type));
        })->editColumn('job_name', function (Job $job) {
            return $job->getJobName();
        })->editColumn('job_status', function (Job $job) {
            $text = '';
            switch ($job->job_status) {
                case Job::QUEUED:
                    $text = '<i class="fa fa-pause" aria-hidden="true"></i> ';
                    break;
                case Job::PROCESSING:
                    $text = '<i class="fa fa-spinner fa-spin animated" aria-hidden="true"></i> ';
                    break;
                case Job::FAILED:
                    $text = '<i class="fa fa-exclamation-circle" aria-hidden="true"></i> ';
                    break;
                case Job::COMPLETED:
                    $text = '<i class="fa fa-check-circle" aria-hidden="true"></i> ';
                    break;
            }
            return $text . ucfirst($job->job_status);
        })->addColumn('action', function (Job $job) {
            return view('jobs.list_action_column', [
                'job' => $job,
            ])->render();
        })->rawColumns(['action', 'job_status']);
        return $table->make(true);
    }

    /**
     * Redirect to the real job viewer
     *
     * @param \App\Models\Job $job
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function viewJob(Job $job): RedirectResponse
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        $route = 'view-' . $job->job_type . '-job';
        return redirect()->route($route, ['job' => $job]);
    }

    /**
     * Delete a job
     *
     * @param \App\Models\Job $job
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function deleteJob(Job $job): RedirectResponse
    {
        if (!$job || !$job->exists) {
            abort(404, 'Unable to find the job.');
        }
        if (!$job->canBeDeleted()) {
            abort(403, 'You are not allowed to delete this job');
        }
        $job->delete();
        return redirect()->back();
    }
}
