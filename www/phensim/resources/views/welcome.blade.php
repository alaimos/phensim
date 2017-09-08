@extends('layouts.app')

@section('content')
    <!-- Hero Content -->
    <div class="bg-primary-dark">
        <!-- Search Content -->
        <section class="content content-full content-boxed overflow-hidden">
            <div class="push-50-t push-50">
                <h1 class="font-s48 font-w700 text-white push-10 visibility-hidden text-center" data-toggle="appear"
                    data-class="animated fadeInDown">
                    <span class="text-primary">S</span><span class="">I</span><span class="text-primary">M</span><span
                            class="">P</span><span class="text-primary">A</span><span class="">T</span><span
                            class="text-primary">H</span><span class="">Y</span>
                </h1>
                <h2 class="h3 font-w400 text-white-op push-50 visibility-hidden text-center" data-toggle="appear"
                    data-timeout="750">
                    SIMulations on PATHwaYs
                </h2>
                {{--
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3">
                        <div class="js-wizard-simple block">
                            <!-- Step Tabs -->
                            <ul class="nav nav-tabs nav-justified">
                                <li class="active">
                                    <a href="#submission-form-step1" data-toggle="tab">1. Select a disease</a>
                                </li>
                                <li>
                                    <a href="#submission-form-step2" data-toggle="tab">2. Nodes of Interest</a>
                                </li>
                                <li>
                                    <a href="#submission-form-step3" data-toggle="tab">3. Extra</a>
                                </li>
                            </ul>
                            <!-- END Step Tabs -->

                            <!-- Form -->
                            <form class="submission-form form-horizontal" action="{{ route('submit-extraction') }}"
                                  method="post">
                            {!! csrf_field() !!}
                            <!-- Steps Content -->
                                <div class="block-content tab-content" style="min-height: 242px;">
                                    <!-- Step 1 -->
                                    <div class="tab-pane push-50-t push-50 active" id="submission-form-step1">
                                        <div class="form-group">
                                            <div class="col-sm-8 col-sm-offset-2">
                                                <label for="submission-form-select-disease">Select a disease</label>
                                                <select class="js-select2 form-control" required
                                                        id="submission-form-select-disease"
                                                        name="disease" data-placeholder="Select a disease">
                                                    <option></option>
                                                    @foreach($diseases as $key => $val)
                                                        <option value="{{ $key }}">{{ $val }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END Step 1 -->
                                    <!-- Step 2 -->
                                    <div class="tab-pane push-50-t push-50" id="submission-form-step2">
                                        <div class="form-group">
                                            <div class="col-sm-8 col-sm-offset-2">
                                                <label for="submission-form-select-noi">Choose one or more nodes of
                                                    interest. If no nodes are chosen an automated selection procedure
                                                    will be used.</label>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <select class="form-control"
                                                                id="submission-form-select-noi" name="nois[]" multiple>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END Step 2 -->
                                    <!-- Step 3 -->
                                    <div class="tab-pane push-5-t push-10" id="submission-form-step3">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label" for="submission-form-max-pvalue">
                                                Subpathways max p-value
                                            </label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="number"
                                                       id="submission-form-max-pvalue" name="max-pvalue" step="any"
                                                       value="0.000001">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label"
                                                   for="submission-form-max-pvalue-annot">
                                                Annotation max p-value
                                            </label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="number"
                                                       id="submission-form-max-pvalue-annot" name="max-pvalue-annot"
                                                       step="any" value="0.05">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-8 col-sm-offset-4">
                                                <a href="#form-advanced-options" role="button" data-toggle="collapse"
                                                   aria-expanded="false" aria-controls="form-advanced-options">Advanced options...</a>
                                            </div>
                                        </div>
                                        <div class="collapse" id="form-advanced-options">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"
                                                       for="submission-form-max-pvalue-pathway">
                                                    Pathways Max p-value
                                                </label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="number"
                                                           id="submission-form-max-pvalue-pathway"
                                                           name="max-pvalue-pathways" step="any"
                                                           value="0.01">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"
                                                       for="submission-form-max-pvalue-nois">
                                                    NoIs max p-value
                                                </label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="number"
                                                           id="submission-form-max-pvalue-nois" name="max-pvalue-nois"
                                                           step="any" value="0.05">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"
                                                       for="submission-form-max-pvalue-nodes">
                                                    Nodes max p-value
                                                </label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="number"
                                                           id="submission-form-max-pvalue-nodes" name="max-pvalue-nodes"
                                                           step="any" value="0.10">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label"
                                                       for="submission-form-min-num-nodes">
                                                    Min number of nodes
                                                </label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="number"
                                                           id="submission-form-min-num-nodes"
                                                           name="min-num-nodes" value="5">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-8 col-sm-offset-4">
                                                    <label class="css-input switch switch-sm switch-primary"
                                                           for="submission-form-backward-visit">
                                                        <input type="checkbox" id="submission-form-backward-visit"
                                                               name="backward-visit"><span></span> Backward visit
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END Step 3 -->
                                </div>
                                <!-- END Steps Content -->
                                <!-- Steps Navigation -->
                                <div class="block-content block-content-mini block-content-full border-t">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <button class="wizard-prev btn btn-default disabled" type="button"><i
                                                        class="fa fa-arrow-left"></i> Previous
                                            </button>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <button class="wizard-next btn btn-default" type="button">Next <i
                                                        class="fa fa-arrow-right"></i></button>
                                            <button class="wizard-finish btn btn-primary" type="submit"
                                                    style="display: none;"><i class="fa fa-check"></i> Submit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Steps Navigation -->
                            </form>
                            <!-- END Form -->
                        </div>
                    </div>
                </div>
                --}}
            </div>
        </section>
        <!-- END Search Content -->
    </div>
    <!-- END Hero Content -->

    <div class="bg-white">
        <section class="content content-full content-boxed">
            <div class="row push-50">
                <div class="col-sm-6 col-sm-offset-3 nice-copy-story">
                    <p class="text-justify">

                    </p>
                    <p class="text-justify">

                    </p>
                </div>
            </div>
        </section>
    </div>

@endsection
@push('inline-scripts')
@endpush