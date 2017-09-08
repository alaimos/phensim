@extends('layouts.app')

@section('content')
    <!-- Hero Content -->
    <div class="bg-primary-dark">
        <section class="content content-full content-boxed overflow-hidden">
            <!-- Section Content -->
            <div class="push-100-t push-50 text-center">
                <h1 class="h2 text-white push-10 visibility-hidden" data-toggle="appear"
                    data-class="animated fadeInDown">Sign up</h1>
                <h2 class="h5 text-white-op visibility-hidden" data-toggle="appear"
                    data-class="animated fadeInDown">You need to have an account to use SIMPATHY.</h2>
            </div>
            <!-- END Section Content -->
        </section>
    </div>
    <!-- END Hero Content -->

    <!-- Content -->
    <div class="bg-white">
        <section class="content content-boxed">
            <!-- Section Content -->
            <div class="row items-push push-50-t push-30">
                <div class="col-md-6 col-md-offset-3">
                    <form class="form-horizontal" action="{{ route('register') }}" method="post">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <div class="form-material form-material-primary">
                                    <input id="name" type="text" class="form-control" name="name"
                                           value="{{ old('name') }}"
                                           placeholder="Enter your name">
                                    <label for="name">Name</label>
                                </div>
                                @if ($errors->has('name'))
                                    <div id="name-error" class="help-block text-right animated fadeInDown">
                                        {{ $errors->first('name') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('affiliation') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <div class="form-material form-material-primary">
                                    <input id="affiliation" type="text" class="form-control" name="affiliation"
                                           value="{{ old('affiliation') }}"
                                           placeholder="Enter your affiliation">
                                    <label for="affiliation">Affiliation</label>
                                </div>
                                @if ($errors->has('affiliation'))
                                    <div id="name-error" class="help-block text-right animated fadeInDown">
                                        {{ $errors->first('affiliation') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <div class="form-material form-material-primary">
                                    <input id="email" type="email" class="form-control" name="email"
                                           value="{{ old('email') }}"
                                           placeholder="Provide your email">
                                    <label for="email">Email</label>
                                </div>
                                @if ($errors->has('email'))
                                    <div id="email-error" class="help-block text-right animated fadeInDown">
                                        {{ $errors->first('email') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <div class="form-material form-material-primary">
                                    <input id="password" type="password" class="form-control" name="password"
                                           placeholder="Choose a strong password..">
                                    <label for="password">Password</label>
                                </div>
                            </div>
                            @if ($errors->has('password'))
                                <div id="password-error" class="help-block text-right animated fadeInDown">
                                    {{ $errors->first('password') }}
                                </div>
                            @endif
                        </div>
                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <div class="col-xs-12">
                                <div class="form-material form-material-primary">
                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" placeholder="..and confirm it">
                                    <label for="password-confirm">Password</label>
                                </div>
                            </div>
                            @if ($errors->has('password_confirmation'))
                                <div id="password-confirm-error" class="help-block text-right animated fadeInDown">
                                    {{ $errors->first('password_confirmation') }}
                                </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="col-sm-6 col-sm-offset-3">
                                <button class="btn btn-block btn-success" type="submit"><i
                                            class="fa fa-plus pull-right"></i> Sign Up
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- END Section Content -->
        </section>
    </div>
    <!-- END Content -->

    <!-- Login -->
    <div class="bg-gray-lighter">
        <section class="content content-full content-boxed">
            <!-- Section Content -->
            <div class="push-20-t push-20 text-center">
                <h3 class="h4 push-20 visibility-hidden" data-toggle="appear">Do you already have an account?</h3>
                <a class="btn btn-rounded btn-noborder btn-lg btn-success visibility-hidden" data-toggle="appear"
                   data-class="animated bounceIn" href="{{ route('login') }}">Log In</a>
            </div>
            <!-- END Section Content -->
        </section>
    </div>
    <!-- END Login -->

@endsection
