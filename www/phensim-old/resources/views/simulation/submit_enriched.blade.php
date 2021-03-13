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
                        data-class="animated fadeInDown">Advanced Simulation</h1>
                    <h2 class="h5 text-white-op visibility-hidden" data-toggle="appear"
                        data-class="animated fadeInDown">Run a simulation using custom enriched pathways</h2>
                </div>
                <!-- END Section Content -->
            </section>
        </div>
        <!-- END Page Header -->

        <section class="content content-boxed overflow-hidden">
            <!-- Section Content -->
            <div class="row items-push-2x push-50-t nice-copy">
                <div class="col-sm-12">
                    {!! Form::open(['class' => 'form-horizontal', 'route' => 'do-submit-enriched', 'method' => 'post', 'files' => true]) !!}
                    <div class="block block-themed">
                        <div class="block-header bg-primary-dark">
                            <div class="block-options-simple">
                                <button class="btn btn-xs btn-square btn-success" type="submit">
                                    <i class="fa fa-check"></i> Submit
                                </button>
                            </div>
                            <h3 class="block-title">Advanced Simulation</h3>
                        </div>
                        <div class="block-content block-content-narrow">
                            <div class="form-group{{ $errors->has('job_name') ? ' has-error' : '' }}">
                                {!! Form::label('job_name', 'Job name', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::text('job_name', null, ['class' => 'form-control']) !!}
                                    @if($errors->has('job_name'))
                                        <div class="help-block">{{ $errors->first('job_name') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('organism') ? ' has-error' : '' }}">
                                {!! Form::label('organism', 'Select an organism', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::select('organism', $organisms, 'hsa', ['class' => 'form-control']) !!}
                                    @if($errors->has('organism'))
                                        <div class="help-block">{{ $errors->first('organism') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('simulation-input') ? ' has-error' : '' }}">
                                {!! Form::label('simulation-input', 'Simulation Parameters', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::file('simulation-input', ['class' => 'form-control']) !!}
                                    @if($errors->has('simulation-input'))
                                        <div class="help-block">{{ $errors->first('simulation-input') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('enrich-db') ? ' has-error' : '' }}">
                                {!! Form::label('enrich-db', 'Enrichment Database File', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::file('enrich-db', ['class' => 'form-control']) !!}
                                    @if($errors->has('enrich-db'))
                                        <div class="help-block">{{ $errors->first('enrich-db') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group"{{ $errors->has('db-filter') ? ' has-error' : '' }}>
                                {!! Form::label('db-filter', 'Optional Db Filter', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::text('db-filter', null, ['class' => 'form-control']) !!}
                                    @if($errors->has('db-filter'))
                                        <div class="help-block">{{ $errors->first('db-filter') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('nonexp-nodes') ? ' has-error' : '' }}">
                                {!! Form::label('nonexp-nodes', 'Non-expressed nodes', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::file('nonexp-nodes', ['class' => 'form-control']) !!}
                                    @if($errors->has('nonexp-nodes'))
                                        <div class="help-block">{{ $errors->first('nonexp-nodes') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('remove-nodes') ? ' has-error' : '' }}">
                                {!! Form::label('remove-nodes', 'Knocked-out nodes', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::file('remove-nodes', ['class' => 'form-control']) !!}
                                    @if($errors->has('remove-nodes'))
                                        <div class="help-block">{{ $errors->first('remove-nodes') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('custom-node-types') ? ' has-error' : '' }}">
                                {!! Form::label('custom-node-types', 'Custom Node Type File', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::file('custom-node-types', ['class' => 'form-control']) !!}
                                    @if($errors->has('custom-node-types'))
                                        <div class="help-block">{{ $errors->first('custom-node-types') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('custom-edge-types') ? ' has-error' : '' }}">
                                {!! Form::label('custom-edge-types', 'Custom Edge Type File', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::file('custom-edge-types', ['class' => 'form-control']) !!}
                                    @if($errors->has('custom-edge-types'))
                                        <div class="help-block">{{ $errors->first('custom-edge-types') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group{{ $errors->has('custom-edge-subtypes') ? ' has-error' : '' }}">
                                {!! Form::label('custom-edge-subtypes', 'Custom Edge SubTypes File', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::file('custom-edge-subtypes', ['class' => 'form-control']) !!}
                                    @if($errors->has('custom-edge-subtypes'))
                                        <div class="help-block">{{ $errors->first('custom-edge-subtypes') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group"{{ $errors->has('fdr') ? ' has-error' : '' }}>
                                {!! Form::label('fdr', 'FDR method', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::select('fdr', ['BH' => 'Benjamini & Hochberg', 'QV' => 'Q-value (Storey et al.)', 'LOC' => 'Local FDR (Efron et al.)'], 'BH', ['class' => 'form-control']) !!}
                                    @if($errors->has('fdr'))
                                        <div class="help-block">{{ $errors->first('fdr') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group"{{ $errors->has('epsilon') ? ' has-error' : '' }}>
                                {!! Form::label('epsilon', 'Epsilon value', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::number('epsilon', 0.001, ['class' => 'form-control', 'step' => 'any']) !!}
                                    @if($errors->has('epsilon'))
                                        <div class="help-block">{{ $errors->first('epsilon') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group"{{ $errors->has('random-seed') ? ' has-error' : '' }}>
                                {!! Form::label('random-seed', 'RNG seed', ['class' => 'col-md-3 control-label']) !!}
                                <div class="col-md-9">
                                    {!! Form::number('random-seed', null, ['class' => 'form-control', 'step' => 'any']) !!}
                                    @if($errors->has('random-seed'))
                                        <div class="help-block">{{ $errors->first('random-seed') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group"{{ $errors->has('enrich-mirnas') ? ' has-error' : '' }}>
                                <div class="col-sm-9 col-sm-offset-3">
                                    <label class="css-input switch switch-sm switch-primary control-label"
                                           for="enrich-mirnas">
                                        <input type="checkbox" id="enrich-mirnas" checked
                                               name="enrich-mirnas"><span></span> Enrich pathways with miRNAs
                                    </label>
                                    @if($errors->has('enrich-mirnas'))
                                        <div class="help-block">{{ $errors->first('enrich-mirnas') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
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
      });
    </script>
@endpush
