@include('layouts.header')
<!-- Page Container -->
<!--
    Available Classes:

    'enable-cookies'             Remembers active color theme between pages (when set through color theme list)

    'sidebar-l'                  Left Sidebar and right Side Overlay
    'sidebar-r'                  Right Sidebar and left Side Overlay
    'sidebar-mini'               Mini hoverable Sidebar (> 991px)
    'sidebar-o'                  Visible Sidebar by default (> 991px)
    'sidebar-o-xs'               Visible Sidebar by default (< 992px)

    'side-overlay-hover'         Hoverable Side Overlay (> 991px)
    'side-overlay-o'             Visible Side Overlay by default (> 991px)

    'side-scroll'                Enables custom scrolling on Sidebar and Side Overlay instead of native scrolling (> 991px)

    'header-navbar-fixed'        Enables fixed header
    'header-navbar-transparent'  Enables a transparent header (if also fixed, it will get a solid dark background color on scrolling)
-->
<div id="page-container"
     class="sidebar-l sidebar-mini sidebar-o side-scroll header-navbar-fixed header-navbar-transparent">
    <!-- Sidebar -->
    <nav id="sidebar">
        <!-- Sidebar Scroll Container -->
        <div id="sidebar-scroll">
            <!-- Sidebar Content -->
            <!-- Adding .sidebar-mini-hide to an element will hide it when the sidebar is in mini mode -->
            <div class="sidebar-content">
                <!-- Side Header -->
                <div class="side-header side-content">
                    <!-- Layout API, functionality initialized in App() -> uiLayoutApi() -->
                    <button class="btn btn-link text-gray pull-right visible-xs visible-sm" type="button"
                            data-toggle="layout" data-action="sidebar_close">
                        <i class="si si-emoticon-smile"></i>
                    </button>
                    <a class="h5 text-white" href="{{ url('/') }}">
                        <span class="h4 font-w600 text-primary sidebar-maxi-hidden"><i class="fa fa-smile-o"></i></span>
                        <span class="h4 font-w600 sidebar-mini-hide">
                            <span class="text-primary">S</span><span class="">I</span><span
                                    class="text-primary">M</span><span class="">P</span><span
                                    class="text-primary">A</span><span class="">T</span><span
                                    class="text-primary">H</span><span class="">Y</span></span>
                    </a>
                </div>
                <!-- END Side Header -->

                <!-- Side Content -->
                <div class="side-content">
                    <ul class="nav-main">
                        <li>
                            <a href="{{ url('/') }}">
                                <i class="si si-home"></i>
                                <span class="sidebar-mini-hide">Home</span></a>
                        </li>
                        @if(Auth::user() !== null && Auth::user()->hasPermission('read-profile'))
                            <li>
                                <a href="{{ url('/home') }}"><i class="si si-user"></i><span
                                            class="sidebar-mini-hide">User Panel</span></a>
                            </li>
                        @endif
                        @if(Auth::user() !== null && Auth::user()->hasPermission('use-api'))
                            <li>
                                <a href="{{ url('/home/api') }}"><i class="si si-energy"></i><span
                                            class="sidebar-mini-hide">API</span></a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ url('/references') }}"><i class="si si-book-open"></i><span
                                        class="sidebar-mini-hide">References</span></a>
                        </li>
                        <li>
                            <a href="{{ url('/contacts') }}"><i class="si si-envelope-open"></i><span
                                        class="sidebar-mini-hide">Contacts</span></a>
                        </li>
                        @if(Auth::user() !== null)
                            <li>
                                <a href="{{ url('/logout') }}"><i class="si si-logout"></i><span
                                            class="sidebar-mini-hide">Log Out</span></a>
                            </li>
                        @else
                            <li>
                                <a href="{{ url('/login') }}"><i class="si si-login"></i><span
                                            class="sidebar-mini-hide">Log In</span></a>
                            </li>
                            <li>
                                <a href="{{ url('/register') }}"><i class="si si-plus"></i><span
                                            class="sidebar-mini-hide">Sign Up</span></a>
                            </li>
                        @endif
                    </ul>
                </div>
                <!-- END Side Content -->
            </div>
            <!-- Sidebar Content -->
        </div>
        <!-- END Sidebar Scroll Container -->
    </nav>
    <!-- END Sidebar -->

    <!-- Header -->
    <header id="header-navbar" class="content-mini content-mini-full hidden-md hidden-lg">
        <div class="content-boxed">
            <!-- Header Navigation Right -->
            <ul class="nav-header pull-right">
                <li>
                    <!-- Layout API, functionality initialized in App() -> uiLayoutApi() -->
                    <button class="btn btn-link text-white pull-right" type="button" data-toggle="layout"
                            data-action="sidebar_open">
                        <i class="fa fa-navicon"></i>
                    </button>
                </li>
            </ul>
            <!-- END Header Navigation Right -->

            <!-- Header Navigation Left -->
            <ul class="nav-header pull-left">
                <li class="header-content">
                    <a class="h5 text-white" href="{{ url('/') }}">
                        <span class="h4 font-w600 text-primary">S</span><span
                                class="h4 font-w600 sidebar-mini-hide"><span class="">P</span><span
                                    class="text-primary">E</span><span class="">C</span><span
                                    class="text-primary">i</span><span class="">f</span><span
                                    class="text-primary">I</span><span class="">C</span></span>
                    </a>
                </li>
            </ul>
            <!-- END Header Navigation Left -->
        </div>
    </header>
    <!-- END Header -->

    <!-- Main Container -->
    <main id="main-container">
        <div id="app">
            @yield('content')
        </div>
    </main>
    <!-- END Main Container -->

    <!-- Footer -->
    <footer id="page-footer" class="content-mini content-mini-full font-s12 bg-gray-lighter clearfix">
        <div class="pull-right">
            Template: <a class="font-w600" href="http://goo.gl/6LF10W" target="_blank">OneUI 3.0</a> by
            <a class="font-w600" href="http://goo.gl/vNS3I" target="_blank">pixelcave</a>
        </div>
        <div class="pull-left">
            &copy; <span class="js-year-copy"></span> -
            Developed by: <span class="font-w600">S. Alaimo, Ph.D.</span>
        </div>
    </footer>
    <!-- END Footer -->
</div>
<!-- END Page Container -->
@include('layouts.footer')