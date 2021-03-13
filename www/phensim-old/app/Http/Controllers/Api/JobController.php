<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{

    public static function provideRoutes(): array
    {
        return [
            '/jobs'       => [
                'get'    => ['JobController@listJobs', 'api-jobs-list'],
                'others' => ['JobController@unsupportedMethod'],
            ],
            '/jobs/{job}' => [
                'get'    => ['JobController@getJob', 'api-get-job'],
                'others' => ['JobController@unsupportedMethod'],
            ],
        ];
    }

    public function listJobs(Request $request): JsonResponse
    {
        return response()->json(Job::listJobs($request->get('status'))->get());
    }

    public function getJob($job): JsonResponse
    {
        $job = Job::find($job);
        if (!$job || !$job->exists) {
            abort(404, 'An invalid job identifier has been provided');
        }
        if (!$job->canBeRead()) {
            abort(403, 'You are not allowed to view this job');
        }
        $data = $job->toArray();
        $data['analysisUri'] = route('api-get-' . $job->job_type, ['job' => $job]);
        return response()->json($data);
    }

}
