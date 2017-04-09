@extends('layouts.app')

@section('content')
    <!-- Main Container -->
    <main id="main-container">
        <!-- Page Header -->
        <div class="bg-primary-dark">
            <section class="content content-full content-boxed">
                <!-- Section Content -->
                <div class="push-50-t push-15 text-center">
                    <h1 class="h2 text-white push-10 visibility-hidden" data-toggle="appear"
                        data-class="animated fadeInDown">Simple Simulation</h1>
                    <h2 class="h5 text-white-op visibility-hidden" data-toggle="appear"
                        data-class="animated fadeInDown">Run a simulation using existent pathway elements</h2>
                </div>
                <!-- END Section Content -->
            </section>
        </div>
        <!-- END Page Header -->

        <section class="content content-boxed overflow-hidden">
            <!-- Section Content -->
            <div class="row items-push-2x push-50-t nice-copy">
                <div class="col-sm-12">
                    @if (count($errors) > 0)
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="alert alert-danger alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                                        &times;
                                    </button>
                                    <h4 class="font-w300 push-15"><strong>Whoops!</strong> Something went wrong!</h4>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                            </div>
                        </div>
                @endif
                <!-- Simple Classic Wizard -->
                    <div class="js-wizard-simple block">
                        <!-- Step Tabs -->
                        <ul class="nav nav-tabs nav-justified">
                            <li class="active">
                                <a href="#simple-sim-sub-step1" data-toggle="tab">1. Select organisms</a>
                            </li>
                            <li>
                                <a href="#simple-sim-sub-step2" data-toggle="tab">2. Over-expressed Nodes</a>
                            </li>
                            <li>
                                <a href="#simple-sim-sub-step3" data-toggle="tab">3. Under-expressed Nodes</a>
                            </li>
                            <li>
                                <a href="#simple-sim-sub-step4" data-toggle="tab">4. Non-Expressed Nodes</a>
                            </li>
                            <li>
                                <a href="#simple-sim-sub-step5" data-toggle="tab">5. Submit Simulation</a>
                            </li>
                        </ul>
                        <!-- END Step Tabs -->

                        <!-- Form -->
                    {!! Form::open(['route' => 'do-submit-simple', 'method' => 'post', 'class' => 'form-horizontal',
                    'files' => true]) !!}
                    <!-- Steps Content -->
                        <div class="block-content tab-content" style="min-height: 300px">
                            <!-- Step 1 -->
                            <div class="tab-pane push-30-t push-50 active" id="simple-sim-sub-step1">
                                <div class="form-group{{ $errors->has('organism') ? ' has-error' : '' }}">
                                    <div class="col-sm-8 col-sm-offset-2">
                                        {!! Form::label('organism', 'Select an organism', ['class' => 'control-label']) !!}
                                        {!! Form::select('organism', $organisms, 'hsa', ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- END Step 1 -->

                            <!-- Step 2 -->
                            <div class="tab-pane push-30-t push-50" id="simple-sim-sub-step2">
                                <div class="form-group{{ $errors->has('overexp-nodes') ? ' has-error' : '' }}">
                                    <nodes-selector id="overexp-nodes" name="overexp-nodes[]"
                                                    label="Select over-expressed nodes"
                                                    data_url="{{ route('list-nodes') }}"
                                                    org_field="organism"></nodes-selector>
                                </div>
                                <div class="form-group{{ $errors->has('overexp-file') ? ' has-error' : '' }}">
                                    <div class="col-sm-8 col-sm-offset-2">
                                        {!! Form::label('overexp-file', 'or upload a text file:', ['class' => 'control-label']) !!}
                                        {!! Form::file('overexp-file', ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- END Step 2 -->

                            <!-- Step 3 -->
                            <div class="tab-pane push-30-t push-50" id="simple-sim-sub-step3">
                                <div class="form-group{{ $errors->has('underexp-nodes') ? ' has-error' : '' }}">
                                    <nodes-selector id="underexp-nodes" name="underexp-nodes[]"
                                                    label="Select under-expressed nodes"
                                                    data_url="{{ route('list-nodes') }}"
                                                    org_field="organism"></nodes-selector>
                                </div>
                                <div class="form-group{{ $errors->has('underexp-file') ? ' has-error' : '' }}">
                                    <div class="col-sm-8 col-sm-offset-2">
                                        {!! Form::label('underexp-file', 'or upload a text file:', ['class' => 'control-label']) !!}
                                        {!! Form::file('underexp-file', ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- END Step 3 -->

                            <!-- Step 4 -->
                            <div class="tab-pane push-30-t push-50" id="simple-sim-sub-step4">
                                <div class="form-group{{ $errors->has('nonexp-nodes') ? ' has-error' : '' }}">
                                    <nodes-selector id="nonexp-nodes" name="nonexp-nodes[]"
                                                    label="Select non-expressed nodes"
                                                    data_url="{{ route('list-nodes') }}"
                                                    org_field="organism"></nodes-selector>
                                </div>
                                <div class="form-group{{ $errors->has('nonexp-file') ? ' has-error' : '' }}">
                                    <div class="col-sm-8 col-sm-offset-2">
                                        {!! Form::label('nonexp-file', 'or upload a text file:', ['class' => 'control-label']) !!}
                                        {!! Form::file('nonexp-file', ['class' => 'form-control']) !!}
                                    </div>
                                </div>
                            </div>
                            <!-- END Step 4 -->

                            <!-- Step 5 -->
                            <div class="tab-pane push-30-t push-50" id="simple-sim-sub-step5">
                                <div class="form-group"{{ $errors->has('epsilon') ? ' has-error' : '' }}>
                                    <div class="col-sm-8 col-sm-offset-2">
                                        {!! Form::label('epsilon', 'Epsilon value', ['class' => 'control-label']) !!}
                                        {!! Form::number('epsilon', 0.001, ['class' => 'form-control', 'step' => 'any']) !!}
                                    </div>
                                </div>
                                <div class="form-group"{{ $errors->has('random-seed') ? ' has-error' : '' }}>
                                    <div class="col-sm-8 col-sm-offset-2">
                                        {!! Form::label('random-seed', 'RNG seed', ['class' => 'control-label']) !!}
                                        {!! Form::number('random-seed', null, ['class' => 'form-control','placeholder' => 'Specify a value if you want to use a manual seed for the random number generator']) !!}
                                    </div>
                                </div>
                                <div class="form-group"{{ $errors->has('enrich-mirnas') ? ' has-error' : '' }}>
                                    <div class="col-sm-8 col-sm-offset-2">
                                        <label class="css-input switch switch-sm switch-primary"
                                               for="enrich-mirnas">
                                            <input type="checkbox" id="enrich-mirnas" checked
                                                   name="enrich-mirnas"><span></span> Enrich pathways with miRNAs
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- END Step 5 -->
                        </div>
                        <!-- END Steps Content -->

                        <!-- Steps Navigation -->
                        <div class="block-content block-content-mini block-content-full border-t">
                            <div class="row">
                                <div class="col-xs-6">
                                    <button class="wizard-prev btn btn-default" type="button"><i
                                                class="fa fa-arrow-left"></i> Previous
                                    </button>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <button class="wizard-next btn btn-default" type="button">Next <i
                                                class="fa fa-arrow-right"></i></button>
                                    <button class="wizard-finish btn btn-primary" type="submit"><i
                                                class="fa fa-check"></i> Submit
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- END Steps Navigation -->
                    {!! Form::close() !!}
                    <!-- END Form -->
                    </div>
                    <!-- END Simple Classic Wizard -->
                </div>
            </div>
            <!-- END Section Content -->
        </section>

    </main>
    <!-- END Main Container -->
@endsection
@push('inline-scripts')
<script>
    $(function () {
        $('.js-wizard-simple').bootstrapWizard({
            'tabClass':         '',
            'firstSelector':    '.wizard-first',
            'previousSelector': '.wizard-prev',
            'nextSelector':     '.wizard-next',
            'lastSelector':     '.wizard-last',
            'onTabShow':        function ($tab, $navigation, $index) {
                var $total = $navigation.find('li').length;
                var $current = $index + 1;
                var $percent = ($current / $total) * 100;

                // Get vital wizard elements
                var $wizard = $navigation.parents('.block');
                var $progress = $wizard.find('.wizard-progress > .progress-bar');
                var $btnPrev = $wizard.find('.wizard-prev');
                var $btnNext = $wizard.find('.wizard-next');
                var $btnFinish = $wizard.find('.wizard-finish');

                // Update progress bar if there is one
                if ($progress) {
                    $progress.css({width: $percent + '%'});
                }
                if ($current == 2) {
                    VueBus.$emit('prepareSelect', 'overexp-nodes');
                }
                if ($current == 3) {
                    VueBus.$emit('prepareSelect', 'underexp-nodes');
                }
                if ($current == 4) {
                    VueBus.$emit('prepareSelect', 'nonexp-nodes');
                }
                // If it's the last tab then hide the last button and show the finish instead
                if ($current >= $total) {
                    $btnNext.hide();
                    $btnFinish.show();
                } else {
                    $btnNext.show();
                    $btnFinish.hide();
                }
            }
        });
        $('.js-wizard-simple ul.nav').find('li > a').each(function () {
            var $this = $(this), $url = $this.attr('href'), $obj = $($url);
            if ($obj.find('.has-error').length > 0) {
                $this.addClass('text-danger');
            }
        });

    });
</script>
@endpush
