@extends('layouts.app')

@section('content')

    <!-- Reminder Content -->
    <div class="content overflow-hidden">
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
                <!-- Reminder Block -->
                <div class="block block-themed animated fadeIn">
                    <div class="block-header bg-primary">
                        <ul class="block-options">
                            <li>
                                <a href="{{ route('login') }}" data-toggle="tooltip" data-placement="left"
                                   title="Log In"><i class="si si-login"></i></a>
                            </li>
                        </ul>
                        <h3 class="block-title">Forgot your password?</h3>
                    </div>
                    <div class="block-content block-content-full block-content-narrow">
                        <!-- Reminder Title -->
                        <p>Please provide your account’s email to proceed with password reset.</p>
                        <!-- END Reminder Title -->

                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <!-- Reminder Form -->
                        <form class="form-horizontal push-30-t push-50" action="{{ route('password.email') }}"
                              method="post">
                            {{ csrf_field() }}
                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <div class="col-xs-12">
                                    <div class="form-material form-material-primary floating">
                                        <input id="email" type="email" class="form-control" name="email"
                                               value="{{ old('email') }}" required autofocus>
                                        <label for="email">E-Mail Address</label>
                                    </div>
                                    @if ($errors->has('email'))
                                        <div id="email-error" class="help-block text-right animated fadeInDown">
                                            {{ $errors->first('email') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12 col-sm-6 col-md-5">
                                    <button class="btn btn-primary" type="submit"><i
                                                class="si si-envelope-open"></i> Send Reset Link
                                    </button>
                                </div>
                            </div>
                        </form>
                        <!-- END Reminder Form -->
                    </div>
                </div>
                <!-- END Reminder Block -->
            </div>
        </div>
    </div>
    <!-- END Reminder Content -->
    <!-- Reminder Footer -->
    <div class="push-10-t text-center animated fadeInUp">
        <small class="text-muted font-w600">
            &copy; <span class="js-year-copy"></span> - Developed by: <span class="font-w600">S. Alaimo, Ph.D.</span>
        </small>
    </div>
    <!-- END Reminder Footer -->
@endsection
