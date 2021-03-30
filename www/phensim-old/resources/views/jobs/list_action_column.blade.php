@if ($job->job_status != \App\Models\Job::QUEUED)
    <a href="Javascript:;" data-id="{{$job->id}}" title="View log"
       class="btn btn-xs btn-primary btn-view-job">
        <i class="fa fa-file-text" aria-hidden="true"></i>
    </a>
@endif
@if ($job->job_status == \App\Models\Job::COMPLETED)
    <a href="{{ route('job-view', ['job' => $job]) }}" title="View results"
       class="btn btn-xs btn-success">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>
@endif
@if ($job->job_status != \App\Models\Job::PROCESSING)
    <a href="{{ route('job-delete', ['job' => $job]) }}" title="Delete" class="btn btn-xs btn-danger">
        <i class="fa fa-trash" aria-hidden="true"></i>
    </a>
@endif