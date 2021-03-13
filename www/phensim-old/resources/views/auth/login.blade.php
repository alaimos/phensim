@extends('layouts.app')

@section('content')
    <!-- Main Container -->
    <main id="main-container">
        <!-- Hero Content -->
        <div class="bg-primary-dark">
            <section class="content content-full content-boxed overflow-hidden">
                <!-- Section Content -->
                <div class="push-100-t push-50 text-center">
                    <h1 class="h2 text-white push-10 visibility-hidden" data-toggle="appear"
                        data-class="animated fadeInDown">Log in</h1>
                    <h2 class="h5 text-white-op visibility-hidden" data-toggle="appear"
                        data-class="animated fadeInDown">To use this software you need to log in.</h2>
                </div>
                <!-- END Section Content -->
            </section>
        </div>
        <!-- END Hero Content -->

        <!-- Log In Form -->
        <div class="bg-white">
            <section class="content content-boxed">
                <!-- Section Content -->
                <div class="row items-push push-50-t push-30">
                    <div class="col-md-6 col-md-offset-3">
                        <!-- Login Form -->
                        <form class="form-horizontal" action="{{ route('login') }}" method="post">
                            {{ csrf_field() }}
                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <div class="col-xs-12">
                                    <div class="form-material form-material-primary">
                                        <input id="email" type="email" class="form-control" name="email"
                                               value="{{ old('email') }}" required autofocus
                                               placeholder="Enter your email address">
                                        <label for="email">E-Mail Address</label>
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
                                               required placeholder="Enter your password">
                                        <label for="password">Password</label>
                                    </div>
                                </div>
                                @if ($errors->has('password'))
                                    <div id="password-error" class="help-block text-right animated fadeInDown">
                                        {{ $errors->first('password') }}
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <div class="col-xs-12 text-center">
                                    <label class="css-input switch switch-sm switch-primary">
                                        <input type="checkbox" name="remember"
                                                {{ old('remember') ? 'checked' : '' }}><span></span> Remember Me?
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-xs-12 col-sm-6 col-sm-offset-3">
                                    <button class="btn btn-block btn-primary" type="submit"><i
                                                class="fa fa-arrow-right pull-right"></i> Log in
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="small text-center">
                            Forgot Password? <a href="{{ route('password.request') }}" class="btn-link">Click here!</a>
                        </div>
                        <!-- END Login Form -->
                    </div>
                </div>
                <!-- END Section Content -->
            </section>
        </div>
        <!-- END Log In Form -->

        <!-- Sign Up Today -->
        <div class="bg-gray-lighter">
            <section class="content content-full content-boxed">
                <!-- Section Content -->
                <div class="push-20-t push-20 text-center">
                    <h3 class="h4 push-20 visibility-hidden" data-toggle="appear">Don’t have an account?</h3>
                    <a class="btn btn-rounded btn-noborder btn-lg btn-success visibility-hidden" data-toggle="appear"
                       data-class="animated bounceIn" href="{{ route('register') }}">Sign Up</a>
                </div>
                <!-- END Section Content -->
            </section>
        </div>
        <!-- END Sign Up Today -->
    </main>
    <!-- END Main Container -->
@endsection
