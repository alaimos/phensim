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
        $table->editColumn('job_type', function (Job $jobData) {
            return ucwords(str_replace(['-', '_'], ' ', $jobData->job_type));
        })->editColumn('status', function (Job $jobData) {
            $text = '';
            switch ($jobData->job_status) {
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
            return $text . ucfirst($jobData->job_status);
        })->addColumn('action', function (Job $jobData) {
            return view('jobs.list_action_column', [
                'jobData' => $jobData,
            ])->render();
        });
        return $table->make(true);
    }
}
