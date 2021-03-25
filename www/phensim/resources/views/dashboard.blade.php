@extends('layouts.app')

@section('content')
    @include('layouts.headers.cards')

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col-12 mb-xl-0">
                <div class="card bg-gradient-default shadow">
                    <div class="card-header bg-transparent">
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="text-white mb-0">Latest updates...</h2>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @forelse($latestUpdates as $update)
                            <div class="d-flex flex-column mt-2">
                                <div class="pt-1 text-sm font-weight-bold text-white d-flex justify-content-between">
                                    <div>
                                        {{ $update->title }}
                                    </div>
                                    <div>
                                        <small class="text-gray"><i class="fas fa-clock mr-1"></i>
                                            {{ $update->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-sm mt-1 mb-0 text-white-50 pb-1"
                                     style="border-bottom: 1px dotted #8898aa">
                                    {{ $update->message }}
                                </div>
                            </div>
                        @empty
                            <div class="d-flex flex-column mt-2">
                                <div class="pt-1 text-sm font-weight-bold text-white d-flex justify-content-between">
                                    <div>
                                        Nothing new here!
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth')
    </div>
@endsection
