<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AngryPug's Point Tracker</title>

    <!-- Bootstrap -->
    {!! Html::style('assets/css/bootstrap.css') !!}
    {!! Html::style('assets/css/style.css') !!}

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <!--<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>-->
    {{--<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>--}}
    <![endif]-->
</head>
<body>

<nav class="navbar navbar-default navbar-fixed-top" id="top-nav">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#top-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            {!! link_to_route('home_path', 'AngryPug\'s Point Tracker', [], ['class' => 'navbar-brand']) !!}
        </div>

        <div class="collapse navbar-collapse" id="top-navbar-collapse">
        @if (Auth::check())
            <ul class="nav navbar-nav">
                <li{!! in_array(Route::currentRouteName(), ['check_points_path', 'home_path']) ? ' class="active"' : '' !!}><a href="{!! route('check_points_path') !!}">Check Points</a></li>
                <li{!! Route::currentRouteName() === 'system_control_path' ? ' class="active"' : '' !!}><a href="{!! route('system_control_path') !!}">System Control</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#0">Welcome, {{ Auth::user()->name }}</a></li>
                <li><a href="{!! route('logout_path') !!}"><i class="fa fa-sign-out"></i> Logout</a></li>
            </ul>
        @else
            <div class="nav navbar-nav">
                <li{!! in_array(Route::currentRouteName(), ['check_points_path', 'home_path']) ? ' class="active"' : '' !!}><a href="{!! route('check_points_path') !!}">Check Points</a></li>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="{!! route('login_path') !!}"><i class="fa fa-twitch"></i> Steamer Login</a></li>
            </ul>
        @endif
        </div><!-- .navbar-collapse -->
    </div><!-- .container-fluid -->
</nav><!-- .navbar -->

@yield('content')

<script src="/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/assets/js/script-min.js"></script>
</body>
</html>