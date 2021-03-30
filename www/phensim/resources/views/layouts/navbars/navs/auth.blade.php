<!-- Top navbar -->
<nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
    <div class="container-fluid">
        <a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block"
           href="{{ route('home') }}">{{ __('Dashboard') }}</a>
        @livewire('layout.user-menu')
    </div>
</nav>
