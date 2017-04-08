<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Datatables;
use Illuminate\Http\JsonResponse;

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
    public function index(): \Illuminate\View\View
    {
        return view('home', [
            'queuedJobs'     => Job::listJobs(Job::QUEUED)->count(),
            'processingJobs' => Job::listJobs(Job::PROCESSING)->count(),
            'completedJobs'  => Job::listJobs(Job::COMPLETED)->count(),
            'failedJobs'     => Job::listJobs(Job::FAILED)->count(),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function jobsData(): JsonResponse
    {
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(Job::listJobs());
        $table->editColumn('job_type', function (Job $job) {
            return ucwords(str_replace(['-', '_'], ' ', $job->job_type));
        })->editColumn('job_status', function (Job $job) {
            $text = '';
            switch ($job->job_status) {
                case Job::QUEUED:
                    $text = '<i class="fa fa-pause" aria-hidden="true"></i> ';
                    break;
                case Job::PROCESSING:
                    $text = '<i class="fa fa-spinner faa-spin animated" aria-hidden="true"></i> ';
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
}
