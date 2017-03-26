<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') :: CCA Lunch Ordering</title>
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#ed1f24">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    {{--<meta name="theme-color" content="#ed1f24">--}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=PT+Serif:400,400i,700,700i">
    <link rel="stylesheet" href="{{ elixir('css/vendor.css') }}">
    <link rel="stylesheet" href="{{ elixir('css/common.css') }}">
    {{--<link rel="stylesheet" href="/css/common.css">--}}
    @yield('styles')
    <script>window.Laravel = <?php echo json_encode(['csrfToken' => csrf_token(),]); ?></script>
</head>
<body class="fixed-sn red-skin">
<header>
    <ul id="slide-out" class="side-nav fixed custom-scrollbar">
        <li>
            <div class="logowrapper waves-light">
                <a href="{{ URL::to('/') }}">
                    <img class="cca-logo-image" src="{{ asset('img/logo.png') }}">
                    <div>Lunch Ordering</div>
                </a>
            </div>
        </li>
        <li>
            <ul class="collapsible">
                <li><a class="waves-effect{{ (Request::is('/') ? ' active' : '') }}" href="{{ URL::to('/') }}"><i
                                class="fa fa-home"></i>Home</a></li>
                <li><a class="waves-effect{{ (Request::is('orders') ? ' active' : '') }}"
                       href="{{ URL::to('orders') }}"><i
                                class="fa fa-cutlery"></i>Order Lunches</a></li>
                <li><a class="waves-effect{{ (Request::is('lunchreport') ? ' active' : '') }}"
                       href="{{ URL::to('lunchreport') }}"><i class="fa fa-file-text"></i>Lunch Report</a></li>
                <li><a class="waves-effect{{ (Request::is('myaccount') ? ' active' : '') }}"
                       href="{{ URL::to('myaccount') }}"><i class="fa fa-id-card"></i>My Account</a></li>

                @if(Auth::check())
                    @if(Auth::user()->privilege_level >= config('app.privilege_level_admin'))
                        <hr/>
                        <li><a class="waves-effect{{ (Request::is('admin/gradelevels') ? ' active' : '') }}"
                               href="{{ URL::to('admin/gradelevels') }}"><i class="fa fa-table"></i>Grade Levels</a>
                        </li>
                        <li><a class="waves-effect{{ (Request::is('admin/providers') ? ' active' : '') }}"
                               href="{{ URL::to('admin/providers') }}"><i class="fa fa-table"></i>Providers</a></li>
                        <li><a class="waves-effect{{ (Request::is('admin/menuitems') ? ' active' : '') }}"
                               href="{{ URL::to('admin/menuitems') }}"><i class="fa fa-table"></i>Menu Items</a></li>
                        <li><a class="waves-effect{{ (Request::is('admin/nolunchexceptions') ? ' active' : '') }}"
                               href="{{ URL::to('admin/nolunchexceptions') }}"><i class="fa fa-ban"></i>No Lunch
                                Exceptions</a></li>
                        <li><a class="waves-effect{{ (Request::is('admin/accounts') ? ' active' : '') }}"
                               href="{{ URL::to('admin/accounts') }}"><i class="fa fa-id-card-o"></i>Accounts</a></li>
                        <li><a class="waves-effect{{ (Request::is('admin/users') ? ' active' : '') }}"
                               href="{{ URL::to('admin/users') }}"><i class="fa fa-users"></i>Users</a></li>
                        <li><a class="waves-effect{{ (Request::is('admin/schedule') ? ' active' : '') }}"
                               href="{{ URL::to('admin/schedule') }}"><i class="fa fa-calendar"></i>Schedule Lunches</a>
                        </li>
                        <li><a class="waves-effect{{ (Request::is('admin/payments') ? ' active' : '') }}"
                               href="{{ URL::to('admin/payments') }}"><i class="fa fa-dollar"></i>Receive Payments</a>
                        </li>
                        <li><a class="waves-effect{{ (Request::is('admin/ordermaint') ? ' active' : '') }}"
                               href="{{ URL::to('admin/ordermaint') }}"><i class="fa fa-calendar-check-o"></i>Order
                                Maintenance</a></li>
                        <li><a class="waves-effect{{ (Request::is('admin/reports') ? ' active' : '') }}"
                               href="{{ URL::to('admin/reports') }}"><i class="fa fa-file-text-o"></i>Admin Reports</a>
                        </li>
                        <li><a class="waves-effect{{ (Request::is('admin/utilities') ? ' active' : '') }}"
                               href="{{ URL::to('admin/utilities') }}"><i class="fa fa-wrench"></i>Utilities</a></li>
                    @else
                        <li><a class="waves-effect{{ (Request::is('contact') ? ' active' : '') }}"
                               href="{{ URL::to('/contact') }}"><i class="fa fa-envelope"></i>Contact Us</a></li>
                    @endif
                    <hr/>
                    <li><a class="waves-effect" href="{{ URL::to('logout') }}"
                           onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i
                                    class="fa fa-sign-out"></i>Logout</a>
                    </li>
                @else
                    <li><a class="waves-effect{{ (Request::is('contact') ? ' active' : '') }}"
                           href="{{ URL::to('/contact') }}"><i class="fa fa-envelope"></i>Contact Us</a></li>
                @endif
            </ul>
        </li>
    </ul>

    <nav class="navbar navbar-toggleable-md navbar-dark scrolling-navbar double-nav">
        <div class="float-left">
            <a href="#" data-activates="slide-out" class="button-collapse"><i class="fa fa-bars"></i></a>
        </div>
        <div class="breadcrumb-dn mr-auto">
            <p>CCA Lunch Ordering</p>
        </div>
        <ul class="nav navbar-nav nav-flex-icons ml-auto">
            <li class="nav-item">
                <a class="nav-link{{ (Request::is('orders') ? ' active' : '') }}" href="{{ URL::to('orders') }}"><i
                            class="fa fa-cutlery"></i> <span
                            class="hidden-sm-down">Orders</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link{{ (Request::is('lunchreport') ? ' active' : '') }}"
                   href="{{ URL::to('lunchreport') }}"><i class="fa fa-file-text"></i> <span
                            class="hidden-sm-down">Report</span></a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false"><i class="fa fa-user"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink"
                     data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">

                    <a class="dropdown-item{{ (Request::is('myaccount') ? ' active' : '') }}"
                       href="{{ URL::to('myaccount') }}">My account</a>
                    @if(Auth::check())
                        <a class="dropdown-item" href="{{ url('/logout') }}"
                           onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
                    @endif
                </div>
            </li>
        </ul>
    </nav>
</header>
<main>
    <div class="loader"><div class="cp-spinner cp-skeleton"></div></div>
    <div class="container-fluid">
        @yield('content')
    </div>
</main>
<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>

<script src="{{ elixir('js/vendor.js') }}"></script>
<script src="{{ elixir('js/common.js') }}"></script>
{{--<script src="/js/common.js"></script>--}}

<script>
    $('.button-collapse').sideNav();
    var el = document.querySelector('.custom-scrollbar');
    Ps.initialize(el);
</script>
@yield('scripts')
</body>
</html>