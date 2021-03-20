@extends('layouts.app', ['title' => __('User Manual')])

@section('content')
    <x-page-header class="col-lg-12" gradient="bg-gradient-orange">
{{--        <x-slot name="description">--}}
{{--            From this page you can manage all your simulations.--}}
{{--        </x-slot>--}}
        User Manual
    </x-page-header>

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                TODO
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection
